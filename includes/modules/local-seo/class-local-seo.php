<?php
/**
 * The Local SEO Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Local_Seo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Local_Seo;

use RankMath\Post;
use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Local_Seo class.
 */
class Local_Seo {

	use Ajax, Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->ajax( 'search_pages', 'search_pages' );
		$this->action( 'after_setup_theme', 'location_sitemap' );
		$this->filter( 'rank_math/settings/title', 'add_settings' );
		$this->filter( 'rank_math/json_ld', 'organization_or_person', 9, 2 );
	}

	/**
	 * Init Local SEO Sitemap if possible.
	 */
	public function location_sitemap() {
		if (
			Helper::is_module_active( 'sitemap' ) &&
			'company' === Helper::get_settings( 'titles.knowledgegraph_type' ) &&
			$this->do_filter( 'sitemap/locations', false )
		) {
			new KML_File();
		}
	}

	/**
	 * Add module settings into general optional panel.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_settings( $tabs ) {
		$tabs['local']['file'] = dirname( __FILE__ ) . '/views/titles-options.php';

		return $tabs;
	}

	/**
	 * Ajax search pages.
	 */
	public function search_pages() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'general' );

		$term = Param::get( 'term' );
		if ( empty( $term ) ) {
			exit;
		}

		$pages = get_posts(
			[
				's'              => $term,
				'post_type'      => 'page',
				'posts_per_page' => -1,
			]
		);

		$data = [];
		foreach ( $pages as $page ) {
			$data[] = [
				'id'   => $page->ID,
				'text' => $page->post_title,
			];
		}

		wp_send_json( [ 'results' => $data ] );
	}

	/**
	 * Output structured data for Person or Organization.
	 *
	 * @param array  $data    Array of JSON-LD data.
	 * @param JsonLD $json_ld The JsonLD instance.
	 *
	 * @return array
	 */
	public function organization_or_person( $data, $json_ld ) {
		$entity = [
			'@type' => '',
			'@id'   => '',
			'name'  => '',
			'url'   => get_home_url(),
		];

		$json_ld->add_prop( 'email', $entity );
		$json_ld->add_prop( 'url', $entity );
		$json_ld->add_prop( 'address', $entity );

		if ( $value = Helper::get_settings( 'titles.knowledgegraph_logo' ) ) { // phpcs:ignore
			$entity['logo'] = [
				'@type' => 'ImageObject',
				'url'   => $value,
			];
		}

		switch ( Helper::get_settings( 'titles.knowledgegraph_type' ) ) {
			case 'company':
				$data['publisher'] = $this->organization( $entity );
				break;
			case 'person':
				$data['publisher'] = $this->person( $entity, $json_ld );
				break;
		}

		return $data;
	}

	/**
	 * Structured data for Organization.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function organization( $entity ) {
		$name            = Helper::get_settings( 'titles.knowledgegraph_name' );
		$type            = Helper::get_settings( 'titles.local_business_type' );
		$entity['@type'] = $type ? $type : 'Organization';
		$entity['@id']   = home_url( '/#organization' );
		$entity['name']  = $name ? $name : get_bloginfo( 'name' );

		if ( is_singular() && 'Organization' !== $type ) {
			$entity['@type'] = \array_values( array_filter( [ $type, 'Organization' ] ) );
		}

		$this->add_contact_points( $entity );
		$this->add_geo_cordinates( $entity );
		$this->add_business_hours( $entity );

		// Price Range.
		if ( $price_range = Helper::get_settings( 'titles.price_range' ) ) { // phpcs:ignore
			$entity['priceRange'] = $price_range;
		}

		return $this->sanitize_organization_schema( $entity, $type );
	}

	/**
	 * Structured data for Person.
	 *
	 * @param array  $entity  Array of JSON-LD entity.
	 * @param JsonLD $json_ld JsonLD instance.
	 */
	private function person( $entity, $json_ld ) {
		$name = Helper::get_settings( 'titles.knowledgegraph_name' );
		if ( ! $name ) {
			return false;
		}

		$entity['@type'] = is_singular()
		? [
			'Organization',
			'Person',
		]
		: 'Person';
		$entity['@id']   = home_url( '/#person' );
		$entity['name']  = $name;
		$json_ld->add_prop( 'phone', $entity );

		if ( isset( $entity['logo'] ) ) {
			$entity['image'] = $entity['logo'];

			if ( ! is_singular() ) {
				unset( $entity['logo'] );
			}
		}

		return $entity;
	}

	/**
	 * Add Contact Points.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_contact_points( &$entity ) {
		$phone_numbers = Helper::get_settings( 'titles.phone_numbers' );
		if ( ! isset( $phone_numbers[0]['number'] ) ) {
			return;
		}

		$entity['contactPoint'] = [];
		foreach ( $phone_numbers as $number ) {
			$entity['contactPoint'][] = [
				'@type'       => 'ContactPoint',
				'telephone'   => $number['number'],
				'contactType' => $number['type'],
			];
		}
	}

	/**
	 * Add geo coordinates.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_geo_cordinates( &$entity ) {
		$geo = Str::to_arr( Helper::get_settings( 'titles.geo' ) );
		if ( ! isset( $geo[0], $geo[1] ) ) {
			return;
		}

		$entity['geo'] = [
			'@type'     => 'GeoCoordinates',
			'latitude'  => $geo[0],
			'longitude' => $geo[1],
		];

		$entity['hasMap'] = 'https://www.google.com/maps/search/?api=1&query=' . join( ',', $geo );
	}

	/**
	 * Add business hours.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_business_hours( &$entity ) {
		$opening_hours = $this->get_opening_hours();
		if ( empty( $opening_hours ) ) {
			return;
		}

		$entity['openingHours'] = [];
		foreach ( $opening_hours as $time => $days ) {
			$entity['openingHours'][] = join( ',', $days ) . ' ' . $time;
		}
	}

	/**
	 * Get opening hours.
	 *
	 * @return bool|array
	 */
	private function get_opening_hours() {
		$hours = Helper::get_settings( 'titles.opening_hours' );
		if ( ! is_array( $hours ) ) {
			return false;
		}

		$opening_hours = [];
		foreach ( $hours as $hour ) {
			if ( empty( $hour['time'] ) ) {
				continue;
			}

			$opening_hours[ $hour['time'] ][] = $hour['day'];
		}

		return $opening_hours;
	}

	/**
	 * Sanitize structured data for different organization types.
	 *
	 * @param array  $entity Array of Schema structured data.
	 * @param string $type   Type of organization.
	 *
	 * @return array Sanitized data.
	 */
	private function sanitize_organization_schema( $entity, $type ) {
		$types = [
			'ecp'  => [ 'Zoo', 'Airport', 'Beach', 'BusStation', 'BusStop', 'Cemetery', 'Crematorium', 'TaxiStand', 'TrainStation', 'EventVenue', 'Museum', 'MusicVenue', 'PlaceOfWorship', 'Buddhist Temple', 'CatholicChurch', 'Church', 'Hindu Temple', 'Mosque', 'Synagogue', 'RVPark', 'SubwayStation', 'GovernmentBuilding', 'CityHall', 'Courthouse', 'DefenceEstablishment', 'Embassy', 'LegislativeBuilding', 'ParkingFacility', 'Park', 'PerformingArtsTheater', 'Playground' ],
			'op'   => [ 'Organization', 'Corporation', 'EducationalOrganization', 'CollegeorUniversity', 'ElementarySchool', 'HighSchool', 'MiddleSchool', 'Preschool', 'School', 'SportsTeam', 'MedicalOrganization', 'DiagnosticLab', 'Pharmacy', 'VeterinaryCare', 'PerformingGroup', 'DanceGroup', 'MusicGroup', 'TheaterGroup', 'GovernmentOrganization', 'NGO' ],
			'opec' => [ 'Residence', 'ApartmentComplex', 'GatedResidenceCommunity', 'SingleFamilyResidence', 'Aquarium' ],
			'logo' => [ 'AnimalShelter', 'AutomotiveBusiness', 'Campground', 'ChildCare', 'DryCleaningorLaundry', 'Dentist', 'EmergencyService', 'FireStation', 'PoliceStation', 'EntertainmentBusiness', 'AdultEntertainment', 'AmusementPark', 'ArtGallery', 'Casino', 'ComedyClub', 'NightClub', 'EmploymentAgency', 'TravelAgency', 'Store', 'BikeStore', 'BookStore', 'ClothingStore', 'ComputerStore', 'ConvenienceStore', 'DepartmentStore', 'ElectronicsStore', 'Florist', 'FurnitureStore', 'GardenStore', 'GroceryStore', 'HardwareStore', 'HobbyShop', 'HomeGoodsStore', 'JewelryStore', 'LiquorStore', 'MensClothingStore', 'MobilePhoneStore', 'MovieRentalStore', 'MusicStore', 'OfficeEquipmentStore', 'OutletStore', 'PawnShop', 'PetStore', 'ShoeStore', 'SportingGoodsStore', 'TireShop', 'ToyStore', 'WholesaleStore', 'FinancialService', 'Hospital', 'MovieTheater', 'HomeAndConstructionBusiness', 'Electrician', 'GeneralContractor', 'Plumber', 'InternetCafe', 'Library', 'LocalBusiness', 'LodgingBusiness', 'Hostel', 'Hotel', 'Motel', 'BedAndBreakfast', 'RadioStation', 'RealEstateAgent', 'RecyclingCenter', 'SelfStorage', 'ShoppingCenter', 'SportsActivityLocation', 'BowlingAlley', 'ExerciseGym', 'GolfCourse', 'HealthClub', 'PublicSwimmingPool', 'SkiResort', 'SportsClub', 'TennisComplex', 'StadiumOrArena', 'TelevisionStation', 'TouristInformationCenter', 'MovingCompany', 'InsuranceAgency', 'ProfessionalService', 'HVACBusiness', 'AutoBodyShop', 'AutoDealer', 'AutoPartsStore', 'AutoRental', 'AutoRepair', 'AutoWash', 'GasStation', 'MotorcycleDealer', 'MotorcycleRepair', 'AccountingService', 'AutomatedTeller', 'FoodEstablishment', 'Bakery', 'BarOrPub', 'Brewery', 'CafeorCoffeeShop', 'FastFoodRestaurant', 'IceCreamShop', 'Restaurant', 'Winery', 'GovernmentOffice', 'PostOffice', 'HealthAndBeautyBusiness', 'BeautySalon', 'DaySpa', 'HairSalon', 'HealthClub', 'NailSalon', 'TattooParlor', 'HousePainter', 'Locksmith', 'Notary', 'RoofingContractor', 'LegalService', 'Physician', 'Optician', 'MedicalClinic', 'BankorCreditUnion' ],
		];

		$perform = false;
		foreach ( $types as $func => $to_check ) {
			if ( in_array( $type, $to_check, true ) ) {
				$perform = 'sanitize_organization_' . $func;
				break;
			}
		}

		return $perform ? $this->$perform( $entity ) : $entity;
	}

	/**
	 * Remove `email`, `contactPoint`, `priceRange` properties
	 * from the Schema entity.
	 *
	 * @param array $entity Array of Schema structured data.
	 *
	 * @return array Sanitized data.
	 */
	private function sanitize_organization_ecp( $entity ) {
		unset( $entity['email'], $entity['contactPoint'], $entity['priceRange'] );

		return $entity;
	}

	/**
	 * Remove `openingHours`, `priceRange` properties
	 * from the Schema entity.
	 *
	 * @param array $entity Array of Schema structured data.
	 *
	 * @return array Sanitized data.
	 */
	private function sanitize_organization_op( $entity ) {
		unset( $entity['openingHours'], $entity['priceRange'] );

		return $entity;
	}

	/**
	 * Remove `openingHours`, `priceRange`, `email`, `contactPoint` properties
	 * from the Schema entity.
	 *
	 * @param array $entity Array of Schema structured data.
	 *
	 * @return array Sanitized data.
	 */
	private function sanitize_organization_opec( $entity ) {
		unset( $entity['openingHours'], $entity['priceRange'], $entity['email'], $entity['contactPoint'] );

		return $entity;
	}

	/**
	 * Change `logo` property to `image` & `contactPoint` to `telephone`.
	 *
	 * @param array $entity Array of schema data.
	 *
	 * @return array Sanitized data.
	 */
	private function sanitize_organization_logo( $entity ) {
		if ( isset( $entity['logo'] ) ) {
			$entity['image'] = $entity['logo'];
		}
		if ( isset( $entity['contactPoint'] ) ) {
			$entity['telephone'] = $entity['contactPoint'][0]['telephone'];
			unset( $entity['contactPoint'] );
		}

		return $entity;
	}
}
