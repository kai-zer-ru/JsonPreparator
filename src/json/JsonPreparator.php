<?php
	/**
	 * Created by PhpStorm.
	 * User: kaizer
	 * Date: 19.08.17
	 * Time: 10:41
	 */
	
	namespace Json;
	use Illuminate\Database\Eloquent\JsonEncodingException;
	use Illuminate\Support\Arr;
	use League\Flysystem\FileNotFoundException;
	
	class JsonPreparator {
		private $schema = [];
		private $require;
		public function __construct($schemaPath, $require = false) {
			$this->require = $require;
			if (is_array($schemaPath)) {
				$this->schema = $schemaPath;
			} else {
				if (is_file($schemaPath)) {
					$this->schema = json_decode(file_get_contents($schemaPath), true);
					if (json_last_error()) {
						throw new JsonEncodingException("Syntax error with parse JSON file \"" . $schemaPath . "\"");
					}
				} else {
					throw new FileNotFoundException($schemaPath);
				}
			}
		}
		
		public function prepare($inputData) {
			$data = $this->iteration($inputData);
			return $data;
		}
		
		private function iteration($inputData) {
			$outputData = [];
			foreach ($this->schema as $key => $value) {
				$require = Arr::get($value, "require", $this->require);
				switch ($value["type"]) {
					case "integer":
						if (array_key_exists($key, $inputData)) {
							$outputData[ $key ] = intval($inputData[ $key ]);
						} else {
							if ($require) {
								if (array_key_exists("default", $value)) {
									$outputData[ $key ] = intval($value["default"]);
								} else {
									$outputData[ $key ] = 0;
								}
							}
						}
						break;
					case "float":
						$format = $value["format"];
						if (array_key_exists($key, $inputData)) {
							$outputData[ $key ] = number_format($inputData[ $key ], Arr::get($format, "decimals", "2"), Arr::get($format, "dec_point", "."), Arr::get($format, "thousands_sep", ""));
						} else {
							if ($require) {
								if (array_key_exists("default", $value)) {
									$outputData[ $key ] = number_format($value["default"], Arr::get($format, "decimals", "2"), Arr::get($format, "dec_point", "."), Arr::get($format, "thousands_sep", ""));;
								} else {
									$outputData[ $key ] = 0.00;
								}
							}
						}
						break;
					case "string":
						if (array_key_exists($key, $inputData)) {
							$outputData[ $key ] = strval($inputData[ $key ]);
						} else {
							if ($require) {
								if (array_key_exists("default", $value)) {
									$outputData[ $key ] = strval($value["default"]);
								} else {
									$outputData[ $key ] = "";
								}
							}
						}
						break;
					case "object":
						$val = new JsonPreparator($value["components"], $this->require);
						if (array_key_exists($key, $inputData)) {
							$data = $inputData[ $key ];
							if ($data) {
								$outputData[ $key ] = $val->prepare($data);
							} else {
								$outputData[ $key ]= null;
							}
							
						} else {
							if ($require) {
								if (array_key_exists("default", $value)) {
									$data = $value["default"];
									$outputData[ $key ] = $val->prepare($data);
									
								} else {
									$outputData[ $key ] = null;
								}
							}
						}
						break;
					case "array":
						$outputData[ $key ] = [];
						switch ($value["values"]["type"]) {
							case "string":
								if (array_key_exists($key, $inputData)) {
									$data = $inputData[ $key ];
									foreach ($data as $dataVal) {
										$outputData[ $key ] [] = strval($dataVal);
									}
								} else {
									if ($require) {
										$outputData[ $key ] [] = "";
									}
								}
								break;
							case "integer":
								if (array_key_exists($key, $inputData)) {
									$data = $inputData[ $key ];
									foreach ($data as $dataVal) {
										$outputData[ $key ] [] = intval($dataVal);
									}
								} else {
									if ($require) {
										$outputData[ $key ] [] = 0;
									}
								}
								break;
							case "object":
								if (array_key_exists($key, $inputData)) {
									$data = $inputData[ $key ];
									$val = new JsonPreparator($value["values"]["components"]);
									foreach ($data as $dataVal) {
										$dataVal = $val->prepare($dataVal);
										$outputData[ $key ] [] = $dataVal;
									}
								} else {
									if ($require) {
										$outputData[ $key ] [] = (object)[];
									}
								}
								break;
							default:
								break;
						}
						break;
					default:
						break;
				}
			}
			return $outputData;
		}
	}