# JsonPreparator

## Оригинальное ReadMe
Оригинальное ReadMe [здесь](README.md)

## Описание
Сервис для подготовки JSON согласно предложенной схеме. Добавляет ндостающие элементы, удаляет лишние.

## Установка

```$bash
composer require kaizer666/json-preparator
```

## Использование

```$php
use Json\JsonPreparator;

function test() {
    $schema = "/path/to/schema.json";
    $data = json_decode('{
      "integer_with_def": 2.0002,
      "float": 123000,
      "string": "text",
      "object": {},
      "array_string": [
      "1.1.1.1.1.1.",
      "1"
      ],
      "array_integer": [
      "1.1.1.1.1.",
      "2.2.2.2."
      ],
      "array": [
        {
          "integer_with_def": 1,
          "float": 1000,
          "text": "text1"
        },{
          "integer_with_def": 2,
          "float": 2000,
          "text": "text2"
        },{
          "integer_with_def": 3,
          "float": 3000,
          "text": "text3"
        },{
          "integer_with_def": 4,
          "float": 4000,
          "text": "text4"
        }
      ]
    }', true);
                
    $preparator = new JsonPreparator($schema);
    $output = $preparator->prepare($data);
    return response()->json($output);
}
```

## Пример схемы

```$json
{
  "integer_with_def": {
	"type": "integer",
	"default": "32"
  },
  "integer": {
	"type": "integer"
  },
  "float": {
	"type": "float",
	"format": {
	  "decimals":2,
	  "dec_point":".",
	  "thousands_sep":" "
	}
  },
  "string": {
	"type":"string"
  },
  "object": {
	"type":"object",
	"components": {
	  "integer_with_def": {
		"type": "integer",
		"default": "32"
	  },
	  "integer": {
		"type": "integer"
	  },
	  "float": {
		"type": "float",
		"format": {
		  "decimals":2,
		  "dec_point":".",
		  "thousands_sep":" "
		}
	  }
	}
  },
  "array_string":{
	"type": "array",
	"values": {
	  "type": "string"
	}
  },
  "array_integer":{
	"type": "array",
	"values": {
	  "type": "integer"
	}
  },
  "array": {
	"type": "array",
	"values": {
	  "type": "object",
	  "components": {
		"integer_with_def": {
		  "type": "integer",
		  "default": "32"
		},
		"integer": {
		  "type": "integer"
		},
		"float": {
		  "type": "float",
		  "format": {
			"decimals":2,
			"dec_point":".",
			"thousands_sep":" "
		  }
		},
		"string": {
		  "type":"string"
		},
		"array_string":{
		  "type": "array",
		  "values": {
			"type": "string"
		  }
		},
		"array_integer":{
		  "type": "array",
		  "values": {
			"type": "integer"
		  }
		}
	  }
	}
  }
}

```