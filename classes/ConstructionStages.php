<?php

/**
 * Summary of ConstructionStages
 * Class that contains all the functions for basic CRUD functionality related to construction_stages
 * table in the database.
 */
class ConstructionStages
{
	private $db;
	/**
	 * Summary of validDateRegex
	 * Regex that checks if a date is valid according to the ISO 8601 standard
	 */
	private $validDateRegex = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
	/**
	 * Summary of validColorCodeRegex
	 * Regex that checks if a color code is valid according to hex color codes
	 */
	private $validColorCodeRegex = '/#([a-fA-F0-9]{3}){1,2}\b/';
	public function __construct()
	{
		$this->db = Api::getDb();
	}

	/**
	 * Summary of getAll
	 * Gets all items from the construction_stages table and returns them.
	 */
	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Summary of getSingle
	 * @param $id is the id of the requested construction_stage
	 * Gets one item from the construction_stages table by id and returns it.
	 */
	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Summary of post
	 * Creates a new construction_stage and adds it to the table, it either returns an array
	 * of errors when validation fails or returns the newly created item if successfully created.
	 * @param ConstructionStagesCreate $data is the body for creating a new item
	 * @return array
	 */
	public function post(ConstructionStagesCreate $data)
	{
		$errors = array();

		$name = $data->name;
		$startDate = $data->startDate;
		$endDate = $data->endDate;
		$duration = $data->duration;
		$durationUnit = $data->durationUnit;
		$color = $data->color;
		$externalId = $data->externalId;
		$status = $data->status;

		if (strlen($name) > 255) {
			$errors[] = "Name exceeds limit of 255 characters!";
		}
		if (!(preg_match($this->validDateRegex, $startDate) > 0)) {
			$errors[] = "Invalid start date, must be in ISO8601 format, i.e. 2022-12-31T14:59:00Z";
		}
		if ($endDate != null) {
			if ((!preg_match($this->validDateRegex, $endDate) > 0)) {
				$errors[] = "Invalid end date, must be in ISO8601 format, i.e. 2022-12-31T14:59:00Z";
			}
			if ($startDate > $endDate) {
				$errors[] = "End date cannot be before start date!";
			}
		}
		if ($durationUnit != "HOURS" || $durationUnit != "DAYS" || $durationUnit != "WEEKS") {
			$durationUnit = "DAYS";
		}
		$duration = $this->calculateDuration($startDate, $endDate, $durationUnit);
		if ($externalId != null) {
			if (strlen($externalId) > 255) {
				$errors[] = "External ID exceeds limit of 255 characters!";
			}
		}
		if ($color != null) {
			if ((!preg_match($this->validColorCodeRegex, $color))) {
				$errors[] = "Invalid color code";
			}
		}
		if ($status != "NEW" || $status != "PLANNED" || $status != "DELETED") {
			$status = "NEW";
		}
		if (empty($errors)) {
			$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES 
				(:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
			$stmt->execute([
				'name' => $name,
				'start_date' => $startDate,
				'end_date' => $endDate,
				'duration' => $duration,
				'durationUnit' => $durationUnit,
				'color' => $color,
				'externalId' => $externalId,
				'status' => $status
			]);

			return $this->getSingle($this->db->lastInsertId());
		}
		return $errors;
	}

	/**
	 * Summary of patch
	 * Edits an already existing construction_stage, it either returns an array
	 * of errors when validation fails or returns the newly created item if successfully created.
	 * @param ConstructionStagesEdit $data
	 * @return array
	 */
	public function patch(ConstructionStagesEdit $data)
	{
		$errors = array();
		
		$id = $data->id;
		$name = $data->name;
		$startDate = $data->startDate;
		$endDate = $data->endDate;
		$duration = $data->duration;
		$durationUnit = $data->durationUnit;
		$color = $data->color;
		$externalId = $data->externalId;
		$status = $data->status;

		if (strlen($name) > 255) {
			$errors[] = "Name exceeds limit of 255 characters!";
		}
		if (!(preg_match($this->validDateRegex, $startDate) > 0)) {
			$errors[] = "Invalid start date, must be in ISO8601 format, i.e. 2022-12-31T14:59:00Z";
		}
		if ($endDate != null) {
			if ((!preg_match($this->validDateRegex, $endDate) > 0)) {
				$errors[] = "Invalid end date, must be in ISO8601 format, i.e. 2022-12-31T14:59:00Z";
			}
			if ($startDate > $endDate) {
				$errors[] = "End date cannot be before start date!";
			}
		}
		if ($durationUnit != "HOURS" || $durationUnit != "DAYS" || $durationUnit != "WEEKS") {
			$durationUnit = "DAYS";
		}
		$duration = $this->calculateDuration($startDate, $endDate, $durationUnit);
		if ($externalId != null) {
			if (strlen($externalId) > 255) {
				$errors[] = "External ID exceeds limit of 255 characters!";
			}
		}
		if ($color != null) {
			if ((!preg_match($this->validColorCodeRegex, $color))) {
				$errors[] = "Invalid color code";
			}
		}
		if ($status != "NEW" || $status != "PLANNED" || $status != "DELETED") {
			$status = "NEW";
		}
		if (empty($errors)) {
			$stmt = $this->db->prepare("
			UPDATE construction_stages
			    SET 
				name=:name,
				start_date=:start_date, 
				end_date= :end_date,
				duration= :duration,
				durationUnit= :durationUnit,
				color= :color,
				externalId= :externalId,
				status = :status
			WHERE
			 id = :id
			");
			$stmt->execute([
				'id' => $id,
				'name' => $name,
				'start_date' => $startDate,
				'end_date' => $endDate,
				'duration' => $duration,
				'durationUnit' => $durationUnit,
				'color' => $color,
				'externalId' => $externalId,
				'status' => $status,
			]);
			return $this->getSingle($id);
		}
		return $errors;
	}

	/**
	 * Summary of delete
	 * The name says delete but it actually does not delete an element from the construction_stages 
	 * table, but simply sets the variable status as 'DELETED'.
	 * @param mixed $id is the id of the element
	 * @return array
	 */
	public function delete($id)
	{
		$stmt = $this->db->prepare("
			UPDATE construction_stages
			    SET 
				status = :status
			WHERE
			 id = :id
			");
		$stmt->execute([
			'id' => $id,
			'status' => "DELETED"
		]);
		return $this->getSingle($id);
	}
	/**
	 * Summary of calculateDuration
	 * A helper function that is used to calculate the duration row for the construction_stages table
	 * that returns null if the end date does not exist or the start date is after the end date.
	 * Different intervals are returned depending upon the value of the durationUnit variable.
	 * @param mixed $startDate 
	 * @param mixed $endDate 
	 * @param mixed $durationUnit
	 * @return int|null
	 */
	private function calculateDuration($startDate, $endDate, $durationUnit)
	{
		$end = new DateTime($endDate);
		$start = new DateTime($startDate);
		$interval = $start->diff($end);

		if ($endDate == null) {
			return null;
		}
		if ($startDate > $endDate) {
			return null;
		}
		if ($durationUnit == "DAYS") {
			return $interval->d;
		}
		if ($durationUnit == "HOURS") {
			return $interval->h;
		}
		if ($durationUnit == "WEEKS") {
			return $interval-> d / 7.0;
		}
	}
}