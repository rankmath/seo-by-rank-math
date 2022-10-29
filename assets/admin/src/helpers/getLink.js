/**
 * Return the link.
 *
 * @param  {string} id Id of the link to get.
 * @param  {string} medium Medium of the link to get.
 * @return {string}
 */
 export default function( id = '', medium = '' ) {
    const url = rankMath.links[ id ] || '';
    if ( ! url ) {
        return '#';
    }

    if ( ! medium ) {
        return url;
    }

    const params = {
        utm_source   : 'Plugin',
        utm_medium   : encodeURIComponent( medium ),
        utm_campaign : 'WP',
    }

    return url + '?' + Object.keys( params ).map( key => `${key}=${params[ key ]}` ).join( '&' );
}