<?php

class ConstructionStages
{
	private $db;
	private $validDateRegex = '/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/';
	private $validColorCodeRegex = '/#([a-fA-F0-9]{3}){1,2}\b/';
	public function __construct()
	{
		$this->db = Api::getDb();
	}

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
			return $data;
		}
		return $errors;
	}

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
	private function calculateDuration($startDate, $endDate, $durationUnit)
	{
		$end = new DateTime($endDate);
		$start = new DateTime($startDate);
		$interval = $end->diff($start);

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
			return $interval-> d / 7;
		}
	}
}