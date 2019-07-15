# Php OSM (Open Street Map)

[![Software License](https://img.shields.io/github/license/sierrafayad/osm-pbf.svg?style=popout)](LICENSE.md)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/sierrafayad/osm-pbf.svg?style=popout)](https://packagist.org/packages/sierrafayad/osm-pbf)
[![Total Downloads](https://img.shields.io/packagist/dt/sierrafayad/osm-pbf.svg?style=popout)](https://packagist.org/packages/sierrafayad/osm-pbf)

This package gives you the hability to wotk with Open Street Maps Protocol Buffer Files , .osm.pbf. I get them from 
https://download.geofabrik.de/ and I haven't tried processing planet.osm

## Installation

Require this package with composer.
```shell
composer require sierrafayad/osm-pbf
```

## Usage
You can read osm.pbf files using the Reader class:
```php
use OsmPbf\Reader;
```

Create a binary handle to the .osm.pbf file and pass it in the `Reader()` Constructor:
```php
$file_handler = fopen("PathToFile.osm.pbf", "rb");
$pbfreader = new OSMReader($file_handler);
```
Call the function `readFileHeader()` to get some useful information about the file that is being read
```php
$file_header = $pbfreader->readFileHeader();
```

Functions to get the boundaries box:
```php
$file_header->getBbox()->getLeft();
$file_header->getBbox()->getBottom();
$file_header->getBbox()->getRight();
$file_header->getBbox()->getTop();
```

Functions to get replication information (WIP: Updating the dataset):
```php
$file_header->getOsmosisReplicationTimestamp();
$file_header->getOsmosisReplicationSequenceNumber();
$file_header->getOsmosisReplicationBaseUrl();
```

Starting the data read:
You could use the `skipToBlock($index)` function to start at any given index (Maybe resume a failed operation):
```php
$pbfreader->skipToBlock(0); 
while ($pbfreader->next()) {
    $elements = $pbfreader->getElements();
    $this->processElements($elements);
}
```

The function `processElements($elements)` should be your own implementation but could be something like this:

```php
private function processElements($elements)
    {
        $type = $elements['type'];

        $records = [];
        $tags = [];
        $nodes = [];
        $relations = [];

        foreach ($elements['data'] as $element) {
            $insert_element = [
                'id' => $element['id'],
                'changeset_id' => $element['changeset_id'],
                'visible' => $element['visible'],
                'timestamp' => $element['timestamp'],
                'version' => $element['version'],
                'uid' => $element['uid'],
                'user' => $element['user'],
            ];
            if ($type == "node") {
                $insert_element["latitude"] = $element["latitude"];
                $insert_element["longitude"] = $element["longitude"];
            }
            if (isset($element["timestamp"])) {
                $insert_element["timestamp"] = str_replace("T", " ", $element["timestamp"]);
                $insert_element["timestamp"] = str_replace("Z", "", $element["timestamp"]);
            }
            $records[] = $insert_element;

            foreach ($element["tags"] as $tag) {
                $insert_tag = [
                    $type . "_id" => $element["id"],
                    "k" => $tag["key"],
                    "v" => $tag["value"]
                ];
                $tags[] = $insert_tag;
            }
            foreach ($element["nodes"] as $node) {
                $insert_node = [
                    $type . "_id" => $element["id"],
                    "node_id" => $node["id"],
                    "sequence" => $node["sequence"]
                ];
                $nodes[] = $insert_node;
            }

            foreach ($element["relations"] as $relation) {
                $insert_relation = [
                    $type . "_id" => $element["id"],
                    "member_type" => $relation["member_type"],
                    "member_id" => $relation["member_id"],
                    "member_role" => $relation["member_role"],
                    "sequence" => $relation["sequence"]
                ];
                $relations[] = $insert_relation;
            }
        }
    }
```

### License

The Laravel IDE Helper Generator is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
