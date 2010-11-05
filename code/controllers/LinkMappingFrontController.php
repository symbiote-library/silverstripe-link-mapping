<?php
/**
 * An extension to the front controller that redirects the user to a link
 * mapping if the page they requested cannot be found.
 *
 * @package silverstripe-linkmapping
 */
class LinkMappingFrontController extends ModelAsController {

	/*
	 * This is largely taken from the default ModelAsController implementation.
	 */
	public function getNestedController() {
		$request = $this->request;

		if(!$URLSegment = $request->param('URLSegment')) {
			throw new Exception('ModelAsController->getNestedController(): was not passed a URLSegment value.');
		}

		// Find page by link, regardless of current locale settings
		Translatable::disable_locale_filter();
		$sitetree = DataObject::get_one(
			'SiteTree',
			sprintf(
				'"URLSegment" = \'%s\' %s',
				Convert::raw2sql($URLSegment),
				(SiteTree::nested_urls() ? 'AND "ParentID" = 0' : null)
			)
		);
		Translatable::enable_locale_filter();

		if(!$sitetree) {
			// first check for a link mapping to direct away to.
			$link = $request->getURL();
			$map  = LinkMapping::get_by_link($link);

			if ($map) {
				$this->response = new SS_HTTPResponse();
				$this->response->redirect($map->getLink(), 301);

				return $this->response;
			}

			// If a root page has been renamed, redirect to the new location.
			// See ContentController->handleRequest() for similiar logic.
			$redirect = self::find_old_page($URLSegment);
			if($redirect = self::find_old_page($URLSegment)) {
				$params = $request->getVars();
				if(isset($params['url'])) unset($params['url']);
				$this->response = new SS_HTTPResponse();
				$this->response->redirect(
					Controller::join_links(
						$redirect->Link(
							Controller::join_links(
								$request->param('Action'),
								$request->param('ID'),
								$request->param('OtherID')
							)
						),
						// Needs to be in separate join links to avoid urlencoding
						($params) ? '?' . http_build_query($params) : null
					),
					301
				);

				return $this->response;
			}

			if($response = ErrorPage::response_for(404)) {
				return $response;
			} else {
				$this->httpError(404, 'The requested page could not be found.');
			}
		}

		// Enforce current locale setting to the loaded SiteTree object
		if($sitetree->Locale) Translatable::set_current_locale($sitetree->Locale);

		if(isset($_REQUEST['debug'])) {
			Debug::message("Using record #$sitetree->ID of type $sitetree->class with link {$sitetree->Link()}");
		}

		return self::controller_for($sitetree, $this->request->param('Action'));
	}

}