<?php


	namespace MehrIt\LaraDbExt\Model;


	use Carbon\Carbon;
	use DateTimeInterface;

	trait SerializeDateFormat
	{
		protected $serializeDateFormat;

		protected $serializeDateTimezone;

		/**
		 * Get the format for database stored dates.
		 *
		 * @return string
		 */
		public abstract function getDateFormat();


		/**
		 * Prepare a date for array / JSON serialization.
		 *
		 * @param \DateTimeInterface $date
		 * @return string
		 */
		protected function serializeDate(DateTimeInterface $date) {

			// adapt timezone, to explicitly set serialization timezone
			if ($this->serializeDateTimezone) {
				$date = Carbon::createFromTimestamp($date->getTimestamp())->setTimezone($this->serializeDateTimezone);
			}

			return $date->format($this->serializeDateFormat ?: $this->getDateFormat());
		}

		/**
		 * Temporarily set model date format and timezone for serialization
		 * @param string|null $format The date format
		 * @param string|\DateTimeZone|null $timezone The timezone dates are serialized in. If null, timezone is unchanged
		 * @param callable $callback The callback
		 * @return mixed The callback return
		 */
		public function withDateFormat(?string $format, $timezone, callable $callback) {

			// remember current values
			$beforeFmt = $this->serializeDateFormat;
			$beforeTz  = $this->serializeDateTimezone;

			$this->serializeDateFormat   = $format;
			$this->serializeDateTimezone = $timezone;
			try {
				return call_user_func($callback, $this);
			}
			finally {
				// restore previous values
				$this->serializeDateFormat   = $beforeFmt;
				$this->serializeDateTimezone = $beforeTz;
			}

		}
	}