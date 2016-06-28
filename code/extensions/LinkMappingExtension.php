<?php
/**
 * An extension that redirects the user to a link mapping if the page they
 * requested cannot be found.
 *
 * @package silverstripe-linkmapping
 */
class LinkMappingExtension extends Extension {

	public function onBeforeHTTPError404( $request ) {

		// first check for a link mapping to direct away to.
		$link = $request->getURL();//
		if ( count( $request->getVars() ) > 1 ) {
			$link = $link . str_replace( 'url=' . $request->requestVar( 'url' ) . '&', '?', $_SERVER['QUERY_STRING'] );
		}

		$map = LinkMapping::get_by_link( $link );

		if ( $map ) {
			$response = new SS_HTTPResponse();
			$response->redirect( $map->getLink(), 301 );

			throw new SS_HTTPResponse_Exception( $response );
		}
	}

}
