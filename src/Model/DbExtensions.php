<?php


	namespace MehrIt\LaraDbExt\Model;


	use MehrIt\LaraDbExt\Connection\AdaptsTimezone;

	/**
	 * Master trait to include all required traits
	 * @package MehrIt\LaraDbExt\Model
	 */
	trait DbExtensions
	{
		use AdaptsTimezone;
		use CreatesBuilders;
		use CreatesRelatedFromAttributes;
		use FieldExpressions;
		use Identifiers;
		use SerializeDateFormat;
	}