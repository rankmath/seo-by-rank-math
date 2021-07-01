/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

export default {
	"version": "1.0.0",
	"properties": {
		"author": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Person"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Author Name", "rank-math" ),
						"placeholder": "%name%"
					}
				}
			}
		},
		"image": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "ImageObject"
				}
			},
			"url": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Image URL", "rank-math" )
					}
				}
			}
		},
		"rating": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Rating"
				}
			},
			"ratingValue": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "number",
						"label": __( "Rating", "rank-math" ),
						"help": __( "Rating score", "rank-math" )
					}
				}
			},
			"worstRating": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "number",
						"label": __( "Rating Minimum", "rank-math" ),
						"help": __( "Rating minimum score", "rank-math" ),
						"placeholder": 1
					}
				}
			},
			"bestRating": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "number",
						"label": __( "Rating Maximum", "rank-math" ),
						"help": __( "Rating maximum score", "rank-math" ),
						"placeholder": 5
					}
				}
			}
		},
		"review": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Review", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Review"
				}
			},
			"datePublished": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "datetimepicker",
						"label": __( "Published Date", "rank-math" ),
						"placeholder": "%date(Y-m-d\\TH:i:sP)%",
						"classes": "hide-group"
					}
				}
			},
			"dateModified": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "datetimepicker",
						"label": __( "Modified Date", "rank-math" ),
						"placeholder": "%modified(Y-m-d\\TH:i:sP)%",
						"classes": "hide-group"
					}
				}
			},
			"author": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Person"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Author Name", "rank-math" ),
							"placeholder": "%name%"
						}
					}
				}
			},
			"reviewRating": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Rating"
					}
				},
				"ratingValue": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "number",
							"label": __( "Rating", "rank-math" ),
							"help": __( "Rating score", "rank-math" )
						}
					}
				},
				"worstRating": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "number",
							"label": __( "Rating Minimum", "rank-math" ),
							"help": __( "Rating minimum score", "rank-math" ),
							"placeholder": 1
						}
					}
				},
				"bestRating": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "number",
							"label": __( "Rating Maximum", "rank-math" ),
							"help": __( "Rating maximum score", "rank-math" ),
							"placeholder": 5
						}
					}
				}
			}
		},
		"bookEditions": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Edition", "rank-math" ),
					"help": __( "Either a specific edition of the written work, or the volume of the work", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Book"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Title", "rank-math" ),
						"help": __( "The title of the tome. Use for the title of the tome if it differs from the book. *Optional when tome has the same title as the book", "rank-math" )
					}
				}
			},
			"bookEdition": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Edition", "rank-math" ),
						"help": __( "The edition of the book", "rank-math" )
					}
				}
			},
			"isbn": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "ISBN", "rank-math" ),
						"help": __( "The ISBN of the print book", "rank-math" )
					}
				}
			},
			"url": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "URL", "rank-math" ),
						"help": __( "URL specific to this edition if one exists", "rank-math" )
					}
				}
			},
			"author": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Person"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Author Name", "rank-math" ),
							"placeholder": "%name%"
						}
					}
				}
			},
			"datePublished": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "datepicker",
						"label": __( "Date Published", "rank-math" ),
						"help": __( "Date of first publication of this tome", "rank-math" )
					}
				}
			},
			"bookFormat": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "radio",
						"label": __( "Book Format", "rank-math" ),
						"desc": "The format of the book.",
						"options": {
							"https://schema.org/EBook": "eBook",
							"https://schema.org/Hardcover": "Hardcover",
							"https://schema.org/Paperback": "Paperback",
							"https://schema.org/AudioBook": "Audio Book"
						},
						"default": "https://schema.org/Hardcover"
					}
				}
			}
		},
		"provider": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"type": "radio",
						"label": __( "Course Provider", "rank-math" ),
						"classes": "show-property",
						"options": {
							"Organization": "Organization",
							"Person": "Person"
						},
						"default": "Organization"
					}
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Course Provider Name", "rank-math" )
					}
				}
			},
			"sameAs": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Course Provider URL", "rank-math" )
					}
				}
			}
		},
		"virtual-location": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"value": "VirtualLocation"
					}
				}
			},
			"url": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "text",
						"label": __( "Online Event URL", "rank-math" ),
						"help": __( "The URL of the online event, where people can join. This property is required if your event is happening online", "rank-math" )
					}
				}
			}
		},
		"address": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Address", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "PostalAddress"
				}
			},
			"streetAddress": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "text",
						"label": __( "Street Address", "rank-math" )
					}
				}
			},
			"addressLocality": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "text",
						"label": __( "Locality", "rank-math" )
					}
				}
			},
			"addressRegion": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "text",
						"label": __( "Region", "rank-math" )
					}
				}
			},
			"postalCode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "text",
						"label": __( "Postal Code", "rank-math" )
					}
				}
			},
			"addressCountry": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "text",
						"label": __( "Country", "rank-math" )
					}
				}
			}
		},
		"physical-location": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Place"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Venue Name", "rank-math" ),
						"help": __( "The venue name.", "rank-math" )
					}
				}
			},
			"url": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Venue URL", "rank-math" ),
						"help": __( "Website URL of the venue", "rank-math" )
					}
				}
			},
			"address": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Address", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "PostalAddress"
					}
				},
				"streetAddress": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Street Address", "rank-math" )
						}
					}
				},
				"addressLocality": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Locality", "rank-math" )
						}
					}
				},
				"addressRegion": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Region", "rank-math" )
						}
					}
				},
				"postalCode": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Postal Code", "rank-math" )
						}
					}
				},
				"addressCountry": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Country", "rank-math" )
						}
					}
				}
			}
		},
		"event-performer": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Performer Information", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "radio",
						"label": __( "Performer", "rank-math" ),
						"classes": "show-property",
						"options": {
							"Organization": "Organization",
							"Person": "Person"
						},
						"default": "Person"
					}
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Name", "rank-math" )
					}
				}
			},
			"sameAs": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Website or Social Link", "rank-math" )
					}
				}
			}
		},
		"offers": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Offers", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Offer"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Name", "rank-math" )
					}
				}
			},
			"category": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Category", "rank-math" )
					}
				}
			},
			"url": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "URL", "rank-math" )
					}
				}
			},
			"price": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Price", "rank-math" )
					}
				}
			},
			"priceCurrency": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Currency", "rank-math" )
					}
				}
			},
			"availability": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "select",
						"label": __( "Availability", "rank-math" ),
						"help": __( "Offer availability", "rank-math" ),
						"classes": "col-4",
						"options": {
							"InStock": "In Stock",
							"SoldOut": "Sold Out",
							"PreOrder": "Preorder"
						},
						"default": "InStock"
					}
				}
			},
			"validFrom": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "datepicker",
						"label": __( "Price Valid From", "rank-math" ),
						"help": __( "The date when the item becomes valid.", "rank-math" )
					}
				}
			},
			"priceValidUntil": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "datepicker",
						"label": __( "Price Valid Until", "rank-math" ),
						"help": __( "The date after which the price will no longer be available", "rank-math" )
					}
				}
			},
			"inventoryLevel": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Inventory Level", "rank-math" )
					}
				}
			}
		},
		"monetary-amount-unit": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Salary", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "QuantitativeValue"
				}
			},
			"value": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Salary (Recommended)", "rank-math" ),
						"help": __( "Insert amount, e.g. 50.00, or a salary range, e.g. 40.00-50.00", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"unitText": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "select",
						"label": __( "Payroll (Recommended)", "rank-math" ),
						"help": __( "Salary amount is for", "rank-math" ),
						"options": {
							"": "None",
							"YEAR": "Yearly",
							"MONTH": "Monthly",
							"WEEK": "Weekly",
							"DAY": "Daily",
							"HOUR": "Hourly"
						},
						"classes": "col-4"
					}
				}
			}
		},
		"monetary-amount": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "MonetaryAmount"
				}
			},
			"currency": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Salary Currency", "rank-math" ),
						"help": __( "ISO 4217 Currency code. Example: EUR", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"value": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Salary", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "QuantitativeValue"
					}
				},
				"value": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Salary (Recommended)", "rank-math" ),
							"help": __( "Insert amount, e.g. 50.00, or a salary range, e.g. 40.00-50.00", "rank-math" ),
							"classes": "col-4"
						}
					}
				},
				"unitText": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "select",
							"label": __( "Payroll (Recommended)", "rank-math" ),
							"help": __( "Salary amount is for", "rank-math" ),
							"options": {
								"": "None",
								"YEAR": "Yearly",
								"MONTH": "Monthly",
								"WEEK": "Weekly",
								"DAY": "Daily",
								"HOUR": "Hourly"
							},
							"classes": "col-4"
						}
					}
				}
			}
		},
		"hiring-organization": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Organization"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Hiring Organization", "rank-math" ),
						"placeholder": "%org_name%",
						"help": __( "The name of the company. Leave empty to use your own company information.", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"sameAs": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Organization URL (Recommended)", "rank-math" ),
						"placeholder": "%org_url%",
						"help": __( "The URL of the organization offering the job position. Leave empty to use your own company information", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"logo": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Organization Logo (Recommended)", "rank-math" ),
						"placeholder": "%org_logo%",
						"help": __( "Logo URL of the organization offering the job position. Leave empty to use your own company information", "rank-math" ),
						"classes": "col-6"
					}
				}
			}
		},
		"brand": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "Brand"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Brand Name", "rank-math" )
					}
				}
			}
		},
		"calories": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "NutritionInformation"
				}
			},
			"calories": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Calories", "rank-math" ),
						"help": __( "The number of calories in the recipe. Optional.", "rank-math" )
					}
				}
			}
		},
		"video-object": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Video", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "VideoObject"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Name", "rank-math" ),
						"help": __( "A recipe video Name", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"help": __( "A recipe video Description", "rank-math" )
					}
				}
			},
			"embedUrl": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Video URL", "rank-math" ),
						"help": __( "A video URL. Optional.", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"contentUrl": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Content URL", "rank-math" ),
						"help": __( "A URL pointing to the actual video media file", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"thumbnailUrl": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Recipe Video Thumbnail", "rank-math" ),
						"help": __( "A recipe video thumbnail URL", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"duration": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Duration", "rank-math" ),
						"help": __( "ISO 8601 duration format. Example: PT1H30M", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"uploadDate": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "datepicker",
						"label": __( "Video Upload Date", "rank-math" ),
						"classes": "col-6"
					}
				}
			}
		},
		"instructionText": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"classes": "hide-group-header"
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "HowtoStep"
				}
			},
			"text": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "textarea"
					}
				}
			}
		},
		"instructions": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Recipe Instructions", "rank-math" ),
					"help": __( "Either a specific edition of the written work, or the volume of the work", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "HowToSection"
				}
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Name", "rank-math" ),
						"help": __( "Instruction name of the recipe.", "rank-math" )
					}
				}
			},
			"itemListElement": {
				"map": {
					"isArray": true,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"arrayMap": "instructionText",
					"arrayProps": {
						"map": {
							"classes": "show-delete-property-group"
						}
					},
					"classes": "show-add-property-group",
					"field": {
						"label": __( "Instruction Texts", "rank-math" )
					}
				}
			}
		},
		"geo-coordinates": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Geo Cordinates", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "GeoCoordinates"
				}
			},
			"latitude": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Latitude", "rank-math" )
					}
				}
			},
			"longitude": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Longitude", "rank-math" )
					}
				}
			}
		},
		"opening-hours": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"field": {
					"label": __( "Timings", "rank-math" )
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"value": "OpeningHoursSpecification"
				}
			},
			"dayOfWeek": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "checkbox",
						"label": __( "Open Days", "rank-math" ),
						"options": {
							"monday": "Monday",
							"tuesday": "Tuesday",
							"wednesday": "Wednesday",
							"thursday": "Thursday",
							"friday": "Friday",
							"saturday": "Saturday",
							"sunday": "Sunday"
						},
						"default": []
					}
				}
			},
			"opens": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "timepicker",
						"label": __( "Opening Time", "rank-math" ),
						"classes": "col-6",
						"placeholder": "09:00 AM"
					}
				}
			},
			"closes": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "timepicker",
						"label": __( "Closing Time", "rank-math" ),
						"classes": "col-6",
						"placeholder": "05:00 PM"
					}
				}
			}
		},
		"cuisine": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false
			},
			"cuisine": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Cuisine", "rank-math" )
					}
				}
			}
		}
	},
	"schemas": {
		"Article": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Article", "rank-math" ),
				"defaultEn": "Article"
			},
			"headline": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"keywords": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Keywords", "rank-math" ),
						"placeholder": "%keywords%"
					}
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"type": "radio",
						"label": __( "Article Type", "rank-math" ),
						"classes": "show-property",
						"options": {
							"Article": "Article",
							"BlogPosting": "Blog Post",
							"NewsArticle": "News Article"
						},
						"notice": {
							"status": "warning",
							"className": "article-notice",
							"content": __( "Google does not allow Person as the Publisher for articles. Organization will be used instead.", "rank-math" )
						}
					}
				}
			},
			"author": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Person"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Author Name", "rank-math" ),
							"placeholder": "%name%"
						}
					}
				}
			},
			"datePublished": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Published Date", "rank-math" ),
						"classes": "hide-group",
						"default": "%date(Y-m-d\\TH:i:sP)%"
					}
				}
			},
			"dateModified": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Modified Date", "rank-math" ),
						"classes": "hide-group",
						"default": "%modified(Y-m-d\\TH:i:sP)%"
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"Book": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Book", "rank-math" ),
				"defaultEn": "Book"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"reviewLocation": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "select",
						"label": __( "Review Location", "rank-math" ),
						"help": __( "The review or rating must be displayed on the page to comply with Google's Schema guidelines.", "rank-math" ),
						"options": {
							"bottom": "Below Content",
							"top": "Above Content",
							"both": "Above and Below Content",
							"custom": "Custom (use shortcode)"
						},
						"default": "custom"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]",
					"dependency": [
						{
							"field": "reviewLocation",
							"value": "custom"
						}
					]
				}
			},
			"url": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "URL", "rank-math" )
					}
				}
			},
			"author": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Person"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Author Name", "rank-math" ),
							"placeholder": "%name%"
						}
					}
				}
			},
			"review": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Review", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Review"
					}
				},
				"datePublished": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Published Date", "rank-math" ),
							"placeholder": "%date(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"dateModified": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Modified Date", "rank-math" ),
							"placeholder": "%modified(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"author": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": [
							"hide-group-header",
							"hide-group"
						]
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Person"
						}
					},
					"name": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"field": {
								"label": __( "Author Name", "rank-math" ),
								"placeholder": "%name%"
							}
						}
					}
				},
				"reviewRating": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": "hide-group-header"
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Rating"
						}
					},
					"ratingValue": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating", "rank-math" ),
								"help": __( "Rating score", "rank-math" )
							}
						}
					},
					"worstRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Minimum", "rank-math" ),
								"help": __( "Rating minimum score", "rank-math" ),
								"placeholder": 1
							}
						}
					},
					"bestRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Maximum", "rank-math" ),
								"help": __( "Rating maximum score", "rank-math" ),
								"placeholder": 5
							}
						}
					}
				}
			},
			"hasPart": {
				"map": {
					"isArray": true,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"arrayMap": "bookEditions",
					"arrayProps": {
						"map": {
							"classes": "show-delete-property-group"
						}
					},
					"field": {
						"label": __( "Editions", "rank-math" )
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"Course": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Course", "rank-math" ),
				"defaultEn": "Course"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"reviewLocation": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "select",
						"label": __( "Review Location", "rank-math" ),
						"help": __( "The review or rating must be displayed on the page to comply with Google's Schema guidelines.", "rank-math" ),
						"options": {
							"bottom": "Below Content",
							"top": "Above Content",
							"both": "Above and Below Content",
							"custom": "Custom (use shortcode)"
						},
						"default": "custom"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]",
					"dependency": [
						{
							"field": "reviewLocation",
							"value": "custom"
						}
					]
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"provider": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"type": "radio",
							"label": __( "Course Provider", "rank-math" ),
							"classes": "show-property",
							"options": {
								"Organization": "Organization",
								"Person": "Person"
							},
							"default": "Organization"
						}
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Course Provider Name", "rank-math" )
						}
					}
				},
				"sameAs": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Course Provider URL", "rank-math" )
						}
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			},
			"review": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Review", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Review"
					}
				},
				"datePublished": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Published Date", "rank-math" ),
							"placeholder": "%date(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"dateModified": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Modified Date", "rank-math" ),
							"placeholder": "%modified(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"author": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": [
							"hide-group-header",
							"hide-group"
						]
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Person"
						}
					},
					"name": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"field": {
								"label": __( "Author Name", "rank-math" ),
								"placeholder": "%name%"
							}
						}
					}
				},
				"reviewRating": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": "hide-group-header"
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Rating"
						}
					},
					"ratingValue": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating", "rank-math" ),
								"help": __( "Rating score", "rank-math" )
							}
						}
					},
					"worstRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Minimum", "rank-math" ),
								"help": __( "Rating minimum score", "rank-math" ),
								"placeholder": 1
							}
						}
					},
					"bestRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Maximum", "rank-math" ),
								"help": __( "Rating maximum score", "rank-math" ),
								"placeholder": 5
							}
						}
					}
				}
			}
		},
		"Event": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Event", "rank-math" ),
				"defaultEn": "Event"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"reviewLocation": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "select",
						"label": __( "Review Location", "rank-math" ),
						"help": __( "The review or rating must be displayed on the page to comply with Google's Schema guidelines.", "rank-math" ),
						"options": {
							"bottom": "Below Content",
							"top": "Above Content",
							"both": "Above and Below Content",
							"custom": "Custom (use shortcode)"
						},
						"default": "custom"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]",
					"dependency": [
						{
							"field": "reviewLocation",
							"value": "custom"
						}
					]
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "select",
						"label": __( "Event Type", "rank-math" ),
						"help": __( "Type of the event", "rank-math" ),
						"classes": "show-property col-4",
						"options": {
							"Event": "Event",
							"BusinessEvent": "Business Event",
							"ChildrensEvent": "Childrens Event",
							"ComedyEvent": "Comedy Event",
							"DanceEvent": "Dance Event",
							"DeliveryEvent": "Delivery Event",
							"EducationEvent": "Education Event",
							"ExhibitionEvent": "Exhibition Event",
							"Festival": "Festival",
							"FoodEvent": "Food Event",
							"LiteraryEvent": "Literary Event",
							"MusicEvent": "Music Event",
							"PublicationEvent": "Publication Event",
							"SaleEvent": "Sale Event",
							"ScreeningEvent": "Screening Event",
							"SocialEvent": "Social Event",
							"SportsEvent": "Sports Event",
							"TheaterEvent": "Theater Event",
							"VisualArtsEvent": "Visual Arts Event"
						}
					}
				}
			},
			"eventStatus": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "select",
						"label": __( "Event Status", "rank-math" ),
						"help": __( "Current status of the event (optional)", "rank-math" ),
						"options": {
							"": "None",
							"EventScheduled": "Scheduled",
							"EventCancelled": "Cancelled",
							"EventPostponed": "Postponed",
							"EventRescheduled": "Rescheduled",
							"EventMovedOnline": "Moved Online"
						},
						"classes": "col-4",
						"default": "EventScheduled"
					}
				}
			},
			"eventAttendanceMode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "select",
						"label": __( "Event Attendance Mode", "rank-math" ),
						"help": __( "Indicates whether the event occurs online, offline at a physical location, or a mix of both online and offline.", "rank-math" ),
						"options": {
							"OfflineEventAttendanceMode": "Offline",
							"OnlineEventAttendanceMode": "Online",
							"MixedEventAttendanceMode": "Online + Offline"
						},
						"default": "OfflineEventAttendanceMode",
						"classes": "col-4"
					}
				}
			},
			"VirtualLocation": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header",
					"dependency": [
						{
							"field": "eventAttendanceMode",
							"value": [
								"OnlineEventAttendanceMode",
								"MixedEventAttendanceMode"
							]
						}
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"value": "VirtualLocation"
						}
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Online Event URL", "rank-math" ),
							"help": __( "The URL of the online event, where people can join. This property is required if your event is happening online", "rank-math" )
						}
					}
				}
			},
			"location": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header",
					"dependency": [
						{
							"field": "eventAttendanceMode",
							"value": [
								"OfflineEventAttendanceMode",
								"MixedEventAttendanceMode"
							]
						}
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Place"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Venue Name", "rank-math" ),
							"help": __( "The venue name.", "rank-math" )
						}
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Venue URL", "rank-math" ),
							"help": __( "Website URL of the venue", "rank-math" )
						}
					}
				},
				"address": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Address", "rank-math" )
						}
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "PostalAddress"
						}
					},
					"streetAddress": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Street Address", "rank-math" )
							}
						}
					},
					"addressLocality": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Locality", "rank-math" )
							}
						}
					},
					"addressRegion": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Region", "rank-math" )
							}
						}
					},
					"postalCode": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Postal Code", "rank-math" )
							}
						}
					},
					"addressCountry": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Country", "rank-math" )
							}
						}
					}
				}
			},
			"performer": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Performer Information", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "radio",
							"label": __( "Performer", "rank-math" ),
							"classes": "show-property",
							"options": {
								"Organization": "Organization",
								"Person": "Person"
							},
							"default": "Person"
						}
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Name", "rank-math" )
						}
					}
				},
				"sameAs": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Website or Social Link", "rank-math" )
						}
					}
				}
			},
			"startDate": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"type": "datetimepicker",
						"label": __( "Start Date", "rank-math" ),
						"help": __( "Date and time of the event", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"endDate": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "datetimepicker",
						"label": __( "End Date", "rank-math" ),
						"help": __( "End date and time of the event", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"offers": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Offers", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Offer"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Name", "rank-math" ),
							"classes": "hide-group",
							"placeholder": "General Admission"
						}
					}
				},
				"category": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Category", "rank-math" ),
							"classes": "hide-group",
							"placeholder": "primary"
						}
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "URL", "rank-math" )
						}
					}
				},
				"price": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Price", "rank-math" )
						}
					}
				},
				"priceCurrency": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Currency", "rank-math" )
						}
					}
				},
				"availability": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "select",
							"label": __( "Availability", "rank-math" ),
							"help": __( "Offer availability", "rank-math" ),
							"classes": "col-4",
							"options": {
								"InStock": "In Stock",
								"SoldOut": "Sold Out",
								"PreOrder": "Preorder"
							},
							"default": "InStock"
						}
					}
				},
				"validFrom": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid From", "rank-math" ),
							"help": __( "The date when the item becomes valid.", "rank-math" )
						}
					}
				},
				"priceValidUntil": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid Until", "rank-math" ),
							"help": __( "The date after which the price will no longer be available", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"inventoryLevel": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Inventory Level", "rank-math" )
						}
					}
				}
			},
			"review": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Review", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Review"
					}
				},
				"datePublished": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Published Date", "rank-math" ),
							"placeholder": "%date(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"dateModified": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Modified Date", "rank-math" ),
							"placeholder": "%modified(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"author": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": [
							"hide-group-header",
							"hide-group"
						]
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Person"
						}
					},
					"name": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"field": {
								"label": __( "Author Name", "rank-math" ),
								"placeholder": "%name%"
							}
						}
					}
				},
				"reviewRating": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": "hide-group-header"
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Rating"
						}
					},
					"ratingValue": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating", "rank-math" ),
								"help": __( "Rating score", "rank-math" )
							}
						}
					},
					"worstRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Minimum", "rank-math" ),
								"help": __( "Rating minimum score", "rank-math" ),
								"placeholder": 1
							}
						}
					},
					"bestRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Maximum", "rank-math" ),
								"help": __( "Rating maximum score", "rank-math" ),
								"placeholder": 5
							}
						}
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"JobPosting": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Job Posting", "rank-math" ),
				"defaultEn": "Job Posting"
			},
			"title": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]"
				}
			},
			"baseSalary": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "MonetaryAmount"
					}
				},
				"currency": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Salary Currency", "rank-math" ),
							"help": __( "ISO 4217 Currency code. Example: EUR", "rank-math" ),
							"classes": "col-4"
						}
					}
				},
				"value": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Salary", "rank-math" )
						}
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "QuantitativeValue"
						}
					},
					"value": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": true,
							"field": {
								"label": __( "Salary (Recommended)", "rank-math" ),
								"help": __( "Insert amount, e.g. 50.00, or a salary range, e.g. 40.00-50.00", "rank-math" ),
								"classes": "col-4"
							}
						}
					},
					"unitText": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": true,
							"field": {
								"type": "select",
								"label": __( "Payroll (Recommended)", "rank-math" ),
								"help": __( "Salary amount is for", "rank-math" ),
								"options": {
									"": "None",
									"YEAR": "Yearly",
									"MONTH": "Monthly",
									"WEEK": "Weekly",
									"DAY": "Daily",
									"HOUR": "Hourly"
								},
								"classes": "col-4"
							}
						}
					}
				}
			},
			"datePosted": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "datepicker",
						"label": __( "Date Posted", "rank-math" ),
						"placeholder": "%date(Y-m-d)%",
						"help": __( "The original date on which employer posted the job. You can leave it empty to use the post publication date as job posted date", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"validThrough": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "datepicker",
						"label": __( "Expiry Posted", "rank-math" ),
						"help": __( "The date when the job posting will expire. If a job posting never expires, or you do not know when the job will expire, do not include this property", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"unpublish": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "select",
						"label": __( "Unpublish when expired", "rank-math" ),
						"options": {
							"on": "Yes",
							"off": "No"
						},
						"help": __( "If checked, post status will be changed to Draft and its URL will return a 404 error, as required by the Rich Result guidelines", "rank-math" ),
						"classes": "col-4",
						"default": "on"
					}
				}
			},
			"employmentType": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "checkbox",
						"multiple": true,
						"label": __( "Employment Type (Recommended)", "rank-math" ),
						"help": __( "Type of employment. You can choose more than one value", "rank-math" ),
						"options": {
							"": "None",
							"FULL_TIME": "Full Time",
							"PART_TIME": "Part Time",
							"CONTRACTOR": "Contractor",
							"TEMPORARY": "Temporary",
							"INTERN": "Intern",
							"VOLUNTEER": "Volunteer",
							"PER_DIEM": "Per Diem",
							"OTHER": "Other"
						},
						"default": []
					}
				}
			},
			"hiringOrganization": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Organization"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Hiring Organization", "rank-math" ),
							"placeholder": "%org_name%",
							"help": __( "The name of the company. Leave empty to use your own company information.", "rank-math" ),
							"classes": "col-4"
						}
					}
				},
				"sameAs": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Organization URL (Recommended)", "rank-math" ),
							"placeholder": "%org_url%",
							"help": __( "The URL of the organization offering the job position. Leave empty to use your own company information", "rank-math" ),
							"classes": "col-6"
						}
					}
				},
				"logo": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Organization Logo (Recommended)", "rank-math" ),
							"placeholder": "%org_logo%",
							"help": __( "Logo URL of the organization offering the job position. Leave empty to use your own company information", "rank-math" ),
							"classes": "col-6"
						}
					}
				}
			},
			"id": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Posting ID (Recommended)", "rank-math" ),
						"help": __( "The hiring organization's unique identifier for the job.", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"jobLocation": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Place"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Venue Name", "rank-math" ),
							"help": __( "The venue name.", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Venue URL", "rank-math" ),
							"help": __( "Website URL of the venue", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"address": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Address", "rank-math" )
						}
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "PostalAddress"
						}
					},
					"streetAddress": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Street Address", "rank-math" )
							}
						}
					},
					"addressLocality": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Locality", "rank-math" )
							}
						}
					},
					"addressRegion": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Region", "rank-math" )
							}
						}
					},
					"postalCode": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Postal Code", "rank-math" )
							}
						}
					},
					"addressCountry": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "text",
								"label": __( "Country", "rank-math" )
							}
						}
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"Music": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Music", "rank-math" ),
				"defaultEn": "Music"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]"
				}
			},
			"url": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "URL", "rank-math" ),
						"placeholder": "%url%"
					}
				}
			},
			"@type": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "radio",
						"label": __( "Music Type", "rank-math" ),
						"classes": "show-property",
						"options": {
							"MusicGroup": "MusicGroup",
							"MusicAlbum": "MusicAlbum"
						},
						"default": "MusicGroup"
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"Person": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Person", "rank-math" ),
				"defaultEn": "Person"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]"
				}
			},
			"email": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Email", "rank-math" )
					}
				}
			},
			"address": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Address", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "PostalAddress"
					}
				},
				"streetAddress": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Street Address", "rank-math" )
						}
					}
				},
				"addressLocality": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Locality", "rank-math" )
						}
					}
				},
				"addressRegion": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Region", "rank-math" )
						}
					}
				},
				"postalCode": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Postal Code", "rank-math" )
						}
					}
				},
				"addressCountry": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Country", "rank-math" )
						}
					}
				}
			},
			"gender": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Gender", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"jobTitle": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Job title", "rank-math" ),
						"help": __( "The job title of the person (for example, Financial Manager).", "rank-math" ),
						"classes": "col-6"
					}
				}
			}
		},
		"Product": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Product", "rank-math" ),
				"defaultEn": "Product"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Product name", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"reviewLocation": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "select",
						"label": __( "Review Location", "rank-math" ),
						"help": __( "The review or rating must be displayed on the page to comply with Google's Schema guidelines.", "rank-math" ),
						"options": {
							"bottom": "Below Content",
							"top": "Above Content",
							"both": "Above and Below Content",
							"custom": "Custom (use shortcode)"
						},
						"default": "custom"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]",
					"dependency": [
						{
							"field": "reviewLocation",
							"value": "custom"
						}
					]
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"sku": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Product SKU", "rank-math" )
					}
				}
			},
			"brand": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Brand"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Brand Name", "rank-math" )
						}
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			},
			"gtin8": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "Gtin", "rank-math" ),
						"classes": "hide-group"
					}
				}
			},
			"mpn": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "MPN", "rank-math" ),
						"classes": "hide-group"
					}
				}
			},
			"isbn": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"label": __( "ISBN", "rank-math" ),
						"classes": "hide-group"
					}
				}
			},
			"offers": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Offers", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Offer"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Name", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"category": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Category", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "URL", "rank-math" ),
							"classes": "hide-group",
							"placeholder": "%url%"
						}
					}
				},
				"price": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Price", "rank-math" )
						}
					}
				},
				"priceCurrency": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Currency", "rank-math" )
						}
					}
				},
				"availability": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "select",
							"label": __( "Availability", "rank-math" ),
							"help": __( "Offer availability", "rank-math" ),
							"classes": "col-4",
							"options": {
								"InStock": "In Stock",
								"SoldOut": "Sold Out",
								"PreOrder": "Preorder"
							},
							"default": "InStock"
						}
					}
				},
				"validFrom": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid From", "rank-math" ),
							"help": __( "The date when the item becomes valid.", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"priceValidUntil": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid Until", "rank-math" ),
							"help": __( "The date after which the price will no longer be available", "rank-math" )
						}
					}
				},
				"inventoryLevel": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Inventory Level", "rank-math" ),
							"classes": "hide-group"
						}
					}
				}
			},
			"review": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Review", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Review"
					}
				},
				"datePublished": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Published Date", "rank-math" ),
							"placeholder": "%date(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"dateModified": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Modified Date", "rank-math" ),
							"placeholder": "%modified(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"author": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": [
							"hide-group-header",
							"hide-group"
						]
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Person"
						}
					},
					"name": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"field": {
								"label": __( "Author Name", "rank-math" ),
								"placeholder": "%name%"
							}
						}
					}
				},
				"reviewRating": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": "hide-group-header"
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Rating"
						}
					},
					"ratingValue": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating", "rank-math" ),
								"help": __( "Rating score", "rank-math" )
							}
						}
					},
					"worstRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Minimum", "rank-math" ),
								"help": __( "Rating minimum score", "rank-math" ),
								"placeholder": 1
							}
						}
					},
					"bestRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Maximum", "rank-math" ),
								"help": __( "Rating maximum score", "rank-math" ),
								"placeholder": 5
							}
						}
					}
				}
			}
		},
		"Recipe": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Recipe", "rank-math" ),
				"defaultEn": "Recipe"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"reviewLocation": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "select",
						"label": __( "Review Location", "rank-math" ),
						"help": __( "The review or rating must be displayed on the page to comply with Google's Schema guidelines.", "rank-math" ),
						"options": {
							"bottom": "Below Content",
							"top": "Above Content",
							"both": "Above and Below Content",
							"custom": "Custom (use shortcode)"
						},
						"default": "custom"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]",
					"dependency": [
						{
							"field": "reviewLocation",
							"value": "custom"
						}
					]
				}
			},
			"datePublished": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Published Date", "rank-math" ),
						"classes": "hide-group",
						"default": "%date(Y-m-d\\TH:i:sP)%"
					}
				}
			},
			"author": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Person"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Author Name", "rank-math" ),
							"placeholder": "%name%"
						}
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"prepTime": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Preparation Time", "rank-math" ),
						"help": __( "ISO 8601 duration format. Example: PT1H30M", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"cookTime": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Cooking Time", "rank-math" ),
						"help": __( "ISO 8601 duration format. Example: PT1H30M", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"totalTime": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Total Time", "rank-math" ),
						"help": __( "ISO 8601 duration format. Example: PT1H30M", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"recipeCategory": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Type", "rank-math" ),
						"help": __( "Type of dish, for example appetizer, or dessert.", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"recipeCuisine": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Cuisine", "rank-math" ),
						"help": __( "The cuisine of the recipe (for example, French or Ethiopian).", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"keywords": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Keywords", "rank-math" ),
						"help": __( "Other terms for your recipe such as the season, the holiday, or other descriptors. Separate multiple entries with commas.", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"recipeYield": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Recipe Yield", "rank-math" ),
						"help": __( "Quantity produced by the recipe, for example 4 servings", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"nutrition": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "hide-group-header"
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "NutritionInformation"
					}
				},
				"calories": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Calories", "rank-math" ),
							"help": __( "The number of calories in the recipe. Optional.", "rank-math" )
						}
					}
				}
			},
			"recipeIngredient": {
				"map": {
					"isArray": true,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "show-add-property show-delete-property",
					"field": {
						"label": __( "Recipe Ingredients", "rank-math" ),
						"help": __( "Recipe ingredients, add one item per line", "rank-math" )
					}
				}
			},
			"review": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Review", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Review"
					}
				},
				"datePublished": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Published Date", "rank-math" ),
							"placeholder": "%date(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"dateModified": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Modified Date", "rank-math" ),
							"placeholder": "%modified(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"author": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": [
							"hide-group-header",
							"hide-group"
						]
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Person"
						}
					},
					"name": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"field": {
								"label": __( "Author Name", "rank-math" ),
								"placeholder": "%name%"
							}
						}
					}
				},
				"reviewRating": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": "hide-group-header"
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Rating"
						}
					},
					"ratingValue": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating", "rank-math" ),
								"help": __( "Rating score", "rank-math" )
							}
						}
					},
					"worstRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Minimum", "rank-math" ),
								"help": __( "Rating minimum score", "rank-math" ),
								"placeholder": 1
							}
						}
					},
					"bestRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Maximum", "rank-math" ),
								"help": __( "Rating maximum score", "rank-math" ),
								"placeholder": 5
							}
						}
					}
				}
			},
			"video": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Video", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "VideoObject"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Name", "rank-math" ),
							"help": __( "A recipe video Name", "rank-math" ),
							"classes": "col-6"
						}
					}
				},
				"description": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "textarea",
							"label": __( "Description", "rank-math" ),
							"help": __( "A recipe video Description", "rank-math" )
						}
					}
				},
				"embedUrl": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Video URL", "rank-math" ),
							"help": __( "A video URL. Optional.", "rank-math" ),
							"classes": "col-6"
						}
					}
				},
				"contentUrl": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Content URL", "rank-math" ),
							"help": __( "A URL pointing to the actual video media file", "rank-math" ),
							"classes": "col-6"
						}
					}
				},
				"thumbnailUrl": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Recipe Video Thumbnail", "rank-math" ),
							"help": __( "A recipe video thumbnail URL", "rank-math" ),
							"classes": "col-6"
						}
					}
				},
				"duration": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Duration", "rank-math" ),
							"help": __( "ISO 8601 duration format. Example: PT1H30M", "rank-math" ),
							"classes": "col-6"
						}
					}
				},
				"uploadDate": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datepicker",
							"label": __( "Video Upload Date", "rank-math" ),
							"classes": "col-6"
						}
					}
				}
			},
			"instructionType": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"type": "radio",
						"label": __( "Instruction Type", "rank-math" ),
						"options": {
							"SingleField": "Single Field",
							"HowToStep": "How To Step"
						},
						"default": "SingleField"
					}
				}
			},
			"instructionsSingleField": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Recipe Instructions", "rank-math" )
					},
					"dependency": [
						{
							"field": "instructionType",
							"value": "SingleField"
						}
					]
				}
			},
			"instructionsHowToStep": {
				"map": {
					"isArray": true,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"arrayMap": "instructions",
					"arrayProps": {
						"map": {
							"classes": "show-delete-property-group"
						}
					},
					"field": {
						"label": __( "Recipe Instructions", "rank-math" )
					},
					"dependency": [
						{
							"field": "instructionType",
							"value": [
								"HowToStep"
							]
						}
					]
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"Restaurant": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Restaurant", "rank-math" ),
				"defaultEn": "Restaurant"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]"
				}
			},
			"telephone": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Phone Number", "rank-math" )
					}
				}
			},
			"priceRange": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Price Range", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"address": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Address", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "PostalAddress"
					}
				},
				"streetAddress": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Street Address", "rank-math" )
						}
					}
				},
				"addressLocality": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Locality", "rank-math" )
						}
					}
				},
				"addressRegion": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Region", "rank-math" )
						}
					}
				},
				"postalCode": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Postal Code", "rank-math" )
						}
					}
				},
				"addressCountry": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "text",
							"label": __( "Country", "rank-math" )
						}
					}
				}
			},
			"geo": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Geo Cordinates", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "GeoCoordinates"
					}
				},
				"latitude": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Latitude", "rank-math" )
						}
					}
				},
				"longitude": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Longitude", "rank-math" )
						}
					}
				}
			},
			"openingHoursSpecification": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Timings", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "OpeningHoursSpecification"
					}
				},
				"dayOfWeek": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "checkbox",
							"label": __( "Open Days", "rank-math" ),
							"options": {
								"monday": "Monday",
								"tuesday": "Tuesday",
								"wednesday": "Wednesday",
								"thursday": "Thursday",
								"friday": "Friday",
								"saturday": "Saturday",
								"sunday": "Sunday"
							},
							"default": []
						}
					}
				},
				"opens": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "timepicker",
							"label": __( "Opening Time", "rank-math" ),
							"classes": "col-6",
							"placeholder": "09:00 AM"
						}
					}
				},
				"closes": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "timepicker",
							"label": __( "Closing Time", "rank-math" ),
							"classes": "col-6",
							"placeholder": "05:00 PM"
						}
					}
				}
			},
			"servesCuisine": {
				"map": {
					"isArray": true,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": "show-add-property show-delete-property",
					"field": {
						"label": __( "Serves Cuisine", "rank-math" )
					}
				}
			},
			"hasMenu": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Menu URL", "rank-math" ),
						"help": __( "URL pointing to the menu of the restaurant.", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"Service": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Service", "rank-math" ),
				"defaultEn": "Service"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]"
				}
			},
			"serviceType": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Service Type", "rank-math" ),
						"help": __( "The type of service being offered, e.g. veterans' benefits, emergency relief, etc.", "rank-math" ),
						"classes": "col-4"
					}
				}
			},
			"offers": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Offers", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Offer"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Name", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"category": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Category", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "URL", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"price": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Price", "rank-math" )
						}
					}
				},
				"priceCurrency": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Currency", "rank-math" )
						}
					}
				},
				"availability": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "select",
							"label": __( "Availability", "rank-math" ),
							"help": __( "Offer availability", "rank-math" ),
							"classes": [
								"col-4",
								"hide-group"
							],
							"options": {
								"InStock": "In Stock",
								"SoldOut": "Sold Out",
								"PreOrder": "Preorder"
							},
							"default": "InStock"
						}
					}
				},
				"validFrom": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid From", "rank-math" ),
							"help": __( "The date when the item becomes valid.", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"priceValidUntil": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid Until", "rank-math" ),
							"help": __( "The date after which the price will no longer be available", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"inventoryLevel": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Inventory Level", "rank-math" ),
							"classes": "hide-group"
						}
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"SoftwareApplication": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Software", "rank-math" ),
				"defaultEn": "Software"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"reviewLocation": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "select",
						"label": __( "Review Location", "rank-math" ),
						"help": __( "The review or rating must be displayed on the page to comply with Google's Schema guidelines.", "rank-math" ),
						"options": {
							"bottom": "Below Content",
							"top": "Above Content",
							"both": "Above and Below Content",
							"custom": "Custom (use shortcode)"
						},
						"default": "custom"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]",
					"dependency": [
						{
							"field": "reviewLocation",
							"value": "custom"
						}
					]
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"operatingSystem": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Operating System", "rank-math" ),
						"help": __( "For example, Windows 7, OSX 10.6, Android 1.6", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"applicationCategory": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Application Category", "rank-math" ),
						"help": __( "For example, Game, Multimedia", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"offers": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Offers", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Offer"
					}
				},
				"name": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Name", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"category": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Category", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "URL", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"price": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Price", "rank-math" )
						}
					}
				},
				"priceCurrency": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"label": __( "Currency", "rank-math" )
						}
					}
				},
				"availability": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "select",
							"label": __( "Availability", "rank-math" ),
							"help": __( "Offer availability", "rank-math" ),
							"classes": [
								"col-4",
								"hide-group"
							],
							"options": {
								"InStock": "In Stock",
								"SoldOut": "Sold Out",
								"PreOrder": "Preorder"
							},
							"default": "InStock"
						}
					}
				},
				"validFrom": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid From", "rank-math" ),
							"help": __( "The date when the item becomes valid.", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"priceValidUntil": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": true,
						"field": {
							"type": "datepicker",
							"label": __( "Price Valid Until", "rank-math" ),
							"help": __( "The date after which the price will no longer be available", "rank-math" ),
							"classes": "hide-group"
						}
					}
				},
				"inventoryLevel": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"label": __( "Inventory Level", "rank-math" ),
							"classes": "hide-group"
						}
					}
				}
			},
			"review": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Review", "rank-math" )
					}
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "Review"
					}
				},
				"datePublished": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Published Date", "rank-math" ),
							"placeholder": "%date(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"dateModified": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": false,
						"isRecommended": false,
						"field": {
							"type": "datetimepicker",
							"label": __( "Modified Date", "rank-math" ),
							"placeholder": "%modified(Y-m-d\\TH:i:sP)%",
							"classes": "hide-group"
						}
					}
				},
				"author": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": [
							"hide-group-header",
							"hide-group"
						]
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Person"
						}
					},
					"name": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"field": {
								"label": __( "Author Name", "rank-math" ),
								"placeholder": "%name%"
							}
						}
					}
				},
				"reviewRating": {
					"map": {
						"isArray": false,
						"isGroup": true,
						"isRequired": false,
						"isRecommended": false,
						"classes": "hide-group-header"
					},
					"@type": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": true,
							"isRecommended": false,
							"value": "Rating"
						}
					},
					"ratingValue": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating", "rank-math" ),
								"help": __( "Rating score", "rank-math" )
							}
						}
					},
					"worstRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Minimum", "rank-math" ),
								"help": __( "Rating minimum score", "rank-math" ),
								"placeholder": 1
							}
						}
					},
					"bestRating": {
						"map": {
							"isArray": false,
							"isGroup": false,
							"isRequired": false,
							"isRecommended": false,
							"field": {
								"type": "number",
								"label": __( "Rating Maximum", "rank-math" ),
								"help": __( "Rating maximum score", "rank-math" ),
								"placeholder": 5
							}
						}
					}
				}
			},
			"image": {
				"map": {
					"isArray": false,
					"isGroup": true,
					"isRequired": false,
					"isRecommended": false,
					"classes": [
						"hide-group-header",
						"hide-group"
					]
				},
				"@type": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"value": "ImageObject"
					}
				},
				"url": {
					"map": {
						"isArray": false,
						"isGroup": false,
						"isRequired": true,
						"isRecommended": false,
						"field": {
							"label": __( "Image URL", "rank-math" ),
							"placeholder": "%post_thumbnail%"
						}
					}
				}
			}
		},
		"VideoObject": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "Video", "rank-math" ),
				"defaultEn": "Video"
			},
			"name": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": true,
					"isRecommended": false,
					"field": {
						"label": __( "Headline", "rank-math" ),
						"placeholder": "%seo_title%"
					}
				}
			},
			"description": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": true,
					"field": {
						"type": "textarea",
						"label": __( "Description", "rank-math" ),
						"placeholder": "%seo_description%"
					}
				}
			},
			"reviewLocationShortcode": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"save": "metadata",
					"field": {
						"type": "text",
						"label": __( "Shortcode", "rank-math" ),
						"help": __( "You can either use this shortcode or Schema Block in the block editor to print the schema data in the content in order to meet the Google's guidelines. Read more about it <a href=https://developers.google.com/search/docs/guides/sd-policies#content target=_blank>here</a>.", "rank-math" ),
						"disabled": "disabled"
					},
					"value": "[rank_math_rich_snippet]"
				}
			},
			"uploadDate": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Upload Date", "rank-math" ),
						"classes": "hide-group",
						"placeholder": "%date(Y-m-d\\TH:i:sP)%"
					}
				}
			},
			"embedUrl": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Embed URL", "rank-math" ),
						"help": __( "A URL pointing to the embeddable player for the video. Example: <code>https://www.youtube.com/embed/VIDEOID</code>", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"contentUrl": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Content URL", "rank-math" ),
						"help": __( "A URL pointing to the actual video media file like MP4, MOV, etc. Please leave it empty if you don't know the URL.", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"duration": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Duration", "rank-math" ),
						"help": __( "ISO 8601 duration format. Example: 1H30M", "rank-math" ),
						"classes": "col-6"
					}
				}
			},
			"thumbnailUrl": {
				"map": {
					"isArray": false,
					"isGroup": false,
					"isRequired": false,
					"isRecommended": false,
					"field": {
						"label": __( "Video Thumbnail", "rank-math" ),
						"help": __( "A video thumbnail URL", "rank-math" ),
						"classes": "hide-group",
						"placeholder": "%post_thumbnail%"
					}
				}
			}
		},
		"WooCommerceProduct": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "WooCommerce Product", "rank-math" ),
				"defaultEn": "WooCommerce Product"
			}
		},
		"EDDProduct": {
			"map": {
				"isArray": false,
				"isGroup": true,
				"isRequired": false,
				"isRecommended": false,
				"title": __( "EDD Product", "rank-math" ),
				"defaultEn": "EDD Product"
			}
		}
	}
}