<?php
/**
 * The Local SEO module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Local_Seo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Local_Seo;

use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Str;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Local_Seo class.
 */
class Local_Seo {

	use Ajax;
	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'after_setup_theme', 'location_sitemap', 11 );
		$this->filter( 'rank_math/settings/title', 'add_settings' );
		$this->filter( 'rank_math/json_ld', 'organization_or_person', 9, 2 );
	}

	/**
	 * Init Local SEO Sitemap.
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
	 * Add module settings in Titles & Meta panel.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_settings( $tabs ) {
		$about_page = Helper::get_settings( 'titles.local_seo_about_page' );
		if ( $about_page ) {
			$about_page = [
				'id'   => $about_page,
				'name' => get_the_title( $about_page ),
				'url'  => get_permalink( $about_page ),
			];
		}

		$contact_page = Helper::get_settings( 'titles.local_seo_contact_page' );
		if ( $contact_page ) {
			$contact_page = [
				'id'   => $contact_page,
				'name' => get_the_title( $contact_page ),
				'url'  => get_permalink( $contact_page ),
			];
		}
		$tabs['local']['json'] = [
			'businessTypes'    => Helper::choices_business_types( true ),
			'phoneTypes'       => Helper::choices_phone_types(),
			'organizationInfo' => Helper::choices_additional_organization_info(),
			'aboutPage'        => $about_page,
			'contactPage'      => $contact_page,
		];

		$tabs['local']['file'] = __DIR__ . '/views/titles-options.php';

		return $tabs;
	}

	/**
	 * Add Person/Organization schema.
	 *
	 * @param array  $data    Array of JSON-LD data.
	 * @param JsonLD $json_ld The JsonLD instance.
	 *
	 * @return array
	 */
	public function organization_or_person( $data, $json_ld ) {
		if ( ! $json_ld->can_add_global_entities( $data ) ) {
			return $data;
		}

		$entity = [
			'@type' => '',
			'@id'   => '',
			'name'  => '',
			'url'   => get_home_url(),
		];

		$social_profiles = $json_ld->get_social_profiles();
		if ( ! empty( $social_profiles ) ) {
			$entity['sameAs'] = $social_profiles;
		}

		$json_ld->add_prop( 'email', $entity );
		$json_ld->add_prop( 'url', $entity );
		$json_ld->add_prop( 'address', $entity );
		$json_ld->add_prop( 'image', $entity );

		switch ( Helper::get_settings( 'titles.knowledgegraph_type' ) ) {
			case 'company':
				$this->add_place_entity( $data, $json_ld );
				$data['publisher'] = $this->organization( $entity, $data );
				break;
			case 'person':
				$data['publisher'] = $this->person( $entity, $json_ld );
				break;
		}

		return $data;
	}

	/**
	 * Add place entity to use in the Organization schema.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld The JsonLD instance.
	 */
	private function add_place_entity( &$data, $jsonld ) {
		$properties = [];
		$this->add_geo_coordinates( $properties );
		$jsonld->add_prop( 'address', $properties );
		if ( empty( $properties ) ) {
			return;
		}

		$data['place'] = array_merge(
			[
				'@type' => 'Place',
				'@id'   => home_url( '/#place' ),
			],
			$properties
		);
	}

	/**
	 * Structured data for Organization.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 * @param array $data  Array of JSON-LD data.
	 */
	private function organization( $entity, $data ) {
		$name            = Helper::get_settings( 'titles.knowledgegraph_name' );
		$type            = Helper::get_settings( 'titles.local_business_type' );
		$entity['@type'] = $type ? $type : 'Organization';
		$entity['@id']   = home_url( '/#organization' );
		$entity['name']  = $name ? $name : get_bloginfo( 'name' );

		if ( is_singular() && 'Organization' !== $type ) {
			$entity['@type'] = \array_values( array_filter( [ $type, 'Organization' ] ) );
		}

		// Price Range.
		if ( $price_range = Helper::get_settings( 'titles.price_range' ) ) { // phpcs:ignore
			$entity['priceRange'] = $price_range;
		}

		$this->add_contact_points( $entity );
		$this->add_business_hours( $entity );
		$this->add_additional_details( $entity );

		// Add reference to the place entity.
		if ( isset( $data['place'] ) ) {
			$entity['location'] = [ '@id' => $data['place']['@id'] ];
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
			$entity['image'] = [ '@id' => $entity['logo']['@id'] ];

			if ( ! is_singular() ) {
				$entity['image'] = $entity['logo'];
				unset( $entity['logo'] );
			}
		}

		return $entity;
	}

	/**
	 * Add Contact points in the Organization schema.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_contact_points( &$entity ) {
		$phone_numbers = Helper::get_settings( 'titles.phone_numbers' );
		if ( empty( $phone_numbers ) ) {
			return;
		}

		$numbers = [];
		foreach ( $phone_numbers as $number ) {
			if ( empty( $number['number'] ) ) {
				continue;
			}

			$numbers[] = [
				'@type'       => 'ContactPoint',
				'telephone'   => $number['number'],
				'contactType' => ! empty( $number['type'] ) ? esc_html( $number['type'] ) : 'customer support',
			];
		}

		if ( ! empty( $numbers ) ) {
			$entity['contactPoint'] = $numbers;
		}
	}

	/**
	 * Add geo coordinates in Place entity.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_geo_coordinates( &$entity ) {
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
	 * Add business hours in the Organization schema.
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
	 * Get Business opening hours.
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

			$opening_hours[ $hour['time'] ][] = ! empty( $hour['day'] ) ? esc_html( $hour['day'] ) : 'Monday';
		}

		return $opening_hours;
	}

	/**
	 * Add additional details in the Organization schema.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_additional_details( &$entity ) {
		$description = Helper::get_settings( 'titles.organization_description' );
		if ( $description ) {
			$entity['description'] = $description;
		}

		$properties = Helper::get_settings( 'titles.additional_info' );
		if ( empty( $properties ) ) {
			return;
		}

		foreach ( $properties as $property ) {
			if ( empty( $property['value'] ) ) {
				continue;
			}

			$type = ! empty( $property['type'] ) ? esc_html( $property['type'] ) : 'legalName';
			if ( 'numberOfEmployees' === $type ) {
				$parts = explode( '-', $property['value'] );
				if ( empty( $parts[1] ) ) {
					$entity['numberOfEmployees'] = [
						'@type' => 'QuantitativeValue',
						'value' => $parts[0],
					];

					continue;
				}

				$entity['numberOfEmployees'] = [
					'@type'    => 'QuantitativeValue',
					'minValue' => $parts[0],
					'maxValue' => $parts[1],
				];

				continue;
			}

			$entity[ $type ] = $property['value'];
		}
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
			'op'   => [ 'Organization', 'Corporation', 'EducationalOrganization', 'CollegeOrUniversity', 'ElementarySchool', 'HighSchool', 'MiddleSchool', 'Preschool', 'School', 'SportsTeam', 'MedicalOrganization', 'DiagnosticLab', 'Pharmacy', 'VeterinaryCare', 'PerformingGroup', 'DanceGroup', 'MusicGroup', 'TheaterGroup', 'GovernmentOrganization', 'NGO', 'Airline', 'Consortium', 'Funding Scheme', 'FundingAgency', 'LibrarySystem', 'NewsMediaOrganization', 'Project', 'SportsOrganization', 'WorkersUnion', 'OnlineStore', 'OnlineBusiness' ],
			'logo' => [ 'AnimalShelter', 'AutomotiveBusiness', 'Campground', 'ChildCare', 'DryCleaningOrLaundry', 'Dentist', 'EmergencyService', 'FireStation', 'PoliceStation', 'EntertainmentBusiness', 'AdultEntertainment', 'AmusementPark', 'ArtGallery', 'Casino', 'ComedyClub', 'MovieTheater', 'NightClub', 'EmploymentAgency', 'TravelAgency', 'Store', 'AutoPartsStore', 'BikeStore', 'BookStore', 'ClothingStore', 'ComputerStore', 'ConvenienceStore', 'DepartmentStore', 'ElectronicsStore', 'Florist', 'FurnitureStore', 'GardenStore', 'GroceryStore', 'HardwareStore', 'HobbyShop', 'HomeGoodsStore', 'JewelryStore', 'LiquorStore', 'MensClothingStore', 'MobilePhoneStore', 'MovieRentalStore', 'MusicStore', 'OfficeEquipmentStore', 'OutletStore', 'PawnShop', 'PetStore', 'ShoeStore', 'SportingGoodsStore', 'TireShop', 'ToyStore', 'WholesaleStore', 'FinancialService', 'Hospital', 'MovieTheater', 'HomeAndConstructionBusiness', 'Electrician', 'GeneralContractor', 'Plumber', 'InternetCafe', 'Library', 'LocalBusiness', 'LodgingBusiness', 'Hostel', 'Hotel', 'Motel', 'BedAndBreakfast', 'Campground', 'RadioStation', 'RealEstateAgent', 'RecyclingCenter', 'SelfStorage', 'ShoppingCenter', 'SportsActivityLocation', 'BowlingAlley', 'ExerciseGym', 'GolfCourse', 'HealthClub', 'PublicSwimmingPool', 'Resort', 'SkiResort', 'SportsClub', 'TennisComplex', 'StadiumOrArena', 'TelevisionStation', 'TouristInformationCenter', 'MovingCompany', 'InsuranceAgency', 'ProfessionalService', 'HVACBusiness', 'AutoBodyShop', 'AutoDealer', 'AutoPartsStore', 'AutoRental', 'AutoRepair', 'AutoWash', 'GasStation', 'MotorcycleDealer', 'MotorcycleRepair', 'AccountingService', 'AutomatedTeller', 'FoodEstablishment', 'Bakery', 'BarOrPub', 'Brewery', 'CafeOrCoffeeShop', 'FastFoodRestaurant', 'IceCreamShop', 'Restaurant', 'Winery', 'GovernmentOffice', 'PostOffice', 'HealthAndBeautyBusiness', 'BeautySalon', 'DaySpa', 'HairSalon', 'HealthClub', 'NailSalon', 'TattooParlor', 'HousePainter', 'Locksmith', 'Notary', 'RoofingContractor', 'LegalService', 'Physician', 'Optician', 'MedicalBusiness', 'MedicalClinic', 'BankOrCreditUnion', 'CovidTestingFacility', 'ArchiveOrganization', 'Optician', 'DietNutrition' ],
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
	 * Change `logo` property to `image` & `contactPoint` to `telephone`.
	 *
	 * @param array $entity Array of schema data.
	 *
	 * @return array Sanitized data.
	 */
	private function sanitize_organization_logo( $entity ) {
		if ( isset( $entity['logo'] ) ) {
			$entity['image'] = [ '@id' => $entity['logo']['@id'] ];
		}
		if ( isset( $entity['contactPoint'] ) ) {
			$entity['telephone'] = $entity['contactPoint'][0]['telephone'];
			unset( $entity['contactPoint'] );
		}

		return $entity;
	}
}
