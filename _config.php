<?php
/**
 * @package silverstripe-linkmapping
 */

Object::add_extension("RequestHandler", "LinkMappingExtension");
Object::add_extension("ContentController", "LinkMappingExtension");
Object::add_extension("ModelAsController", "LinkMappingExtension");