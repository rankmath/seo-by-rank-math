<?php
/**
 * The Local SEO module sanitizer class.
 *
 * @since      1.0.86
 * @package    RankMath
 * @subpackage RankMath\Local_Seo
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Local_Seo;

defined( 'ABSPATH' ) || exit;

/**
 * Local_Seo class.
 */
class Sanitizer {
	/**
	 * Sanitize structured data for different organization types.
	 *
	 * @param array  $entity Array of Schema structured data.
	 * @param string $type   Type of organization.
	 *
	 * @return array Sanitized data.
	 */
	public static function sanitize_organization_schema( $entity, $type ) {
		$types = [
			'op'   => [ 'Organization', 'Corporation', 'EducationalOrganization', 'CollegeOrUniversity', 'ElementarySchool', 'HighSchool', 'MiddleSchool', 'Preschool', 'School', 'SportsTeam', 'MedicalOrganization', 'DiagnosticLab', 'Pharmacy', 'VeterinaryCare', 'PerformingGroup', 'DanceGroup', 'MusicGroup', 'TheaterGroup', 'GovernmentOrganization', 'NGO', 'Airline', 'Consortium', 'Funding Scheme', 'FundingAgency', 'LibrarySystem', 'NewsMediaOrganization', 'Project', 'SportsOrganization', 'WorkersUnion' ],
			'logo' => [ 'AnimalShelter', 'AutomotiveBusiness', 'Campground', 'ChildCare', 'DryCleaningOrLaundry', 'Dentist', 'EmergencyService', 'FireStation', 'PoliceStation', 'EntertainmentBusiness', 'AdultEntertainment', 'AmusementPark', 'ArtGallery', 'Casino', 'ComedyClub', 'MovieTheater', 'NightClub', 'EmploymentAgency', 'TravelAgency', 'Store', 'AutoPartsStore', 'BikeStore', 'BookStore', 'ClothingStore', 'ComputerStore', 'ConvenienceStore', 'DepartmentStore', 'ElectronicsStore', 'Florist', 'FurnitureStore', 'GardenStore', 'GroceryStore', 'HardwareStore', 'HobbyShop', 'HomeGoodsStore', 'JewelryStore', 'LiquorStore', 'MensClothingStore', 'MobilePhoneStore', 'MovieRentalStore', 'MusicStore', 'OfficeEquipmentStore', 'OutletStore', 'PawnShop', 'PetStore', 'ShoeStore', 'SportingGoodsStore', 'TireShop', 'ToyStore', 'WholesaleStore', 'FinancialService', 'Hospital', 'MovieTheater', 'HomeAndConstructionBusiness', 'Electrician', 'GeneralContractor', 'Plumber', 'InternetCafe', 'Library', 'LocalBusiness', 'LodgingBusiness', 'Hostel', 'Hotel', 'Motel', 'BedAndBreakfast', 'Campground', 'RadioStation', 'RealEstateAgent', 'RecyclingCenter', 'SelfStorage', 'ShoppingCenter', 'SportsActivityLocation', 'BowlingAlley', 'ExerciseGym', 'GolfCourse', 'HealthClub', 'PublicSwimmingPool', 'Resort', 'SkiResort', 'SportsClub', 'TennisComplex', 'StadiumOrArena', 'TelevisionStation', 'TouristInformationCenter', 'MovingCompany', 'InsuranceAgency', 'ProfessionalService', 'HVACBusiness', 'AutoBodyShop', 'AutoDealer', 'AutoPartsStore', 'AutoRental', 'AutoRepair', 'AutoWash', 'GasStation', 'MotorcycleDealer', 'MotorcycleRepair', 'AccountingService', 'AutomatedTeller', 'FoodEstablishment', 'Bakery', 'BarOrPub', 'Brewery', 'CafeOrCoffeeShop', 'FastFoodRestaurant', 'IceCreamShop', 'Restaurant', 'Winery', 'GovernmentOffice', 'PostOffice', 'HealthAndBeautyBusiness', 'BeautySalon', 'DaySpa', 'HairSalon', 'HealthClub', 'NailSalon', 'TattooParlor', 'HousePainter', 'Locksmith', 'Notary', 'RoofingContractor', 'LegalService', 'Physician', 'Optician', 'MedicalBusiness', 'MedicalClinic', 'BankOrCreditUnion', 'CovidTestingFacility', 'ArchiveOrganization', 'Optician', 'Attorney' ],
		];

		$perform = false;
		foreach ( $types as $func => $to_check ) {
			if ( in_array( $type, $to_check, true ) ) {
				$perform = 'sanitize_organization_' . $func;
				break;
			}
		}

		return $perform ? self::$perform( $entity ) : $entity;
	}

	/**
	 * Remove `openingHours`, `priceRange` properties
	 * from the Schema entity.
	 *
	 * @param array $entity Array of Schema structured data.
	 *
	 * @return array Sanitized data.
	 */
	public static function sanitize_organization_op( $entity ) {
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
	public static function sanitize_organization_logo( $entity ) {
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
