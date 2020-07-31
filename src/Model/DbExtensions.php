<?php


	namespace MehrIt\LaraDbExt\Model;


	/**
	 * Master trait to include all required traits
	 * @package MehrIt\LaraDbExt\Model
	 */
	trait DbExtensions
	{
		use AdaptsAttributeTimezone;
		use CreatesBuilders;
		use CreatesRelatedFromAttributes;
		use FieldExpressions;
		use Identifiers;
		use SerializeDateFormat;
	}