<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 2/19/2019
 * Time: 8:22 PM
 */
@mkdir(__DIR__ . "/sql/", 0655, true);
$start_time = time();
if (function_exists('mb_ereg_replace')) {
    function mb_escape(string $string)
    {
        return mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x27\x5C]', '\\\0', $string);
    }
} else {
    function mb_escape(string $string)
    {
        return preg_replace('~[\x00\x0A\x0D\x1A\x22\x27\x5C]~u', '\\\$0', $string);
    }
}


global $entity_handlers;
$entity_handlers = array(
    "nodes" => null,
    "node_tags" => null,
    "ways" => null,
    "way_tags" => null,
    "way_nodes" => null,
    "relations" => null,
    "relation_tags" => null,
    "relation_members" => null,
);
foreach ($entity_handlers as $entity => &$handler) {
    $file_name = "sql/insert_" . $entity . ".sql";
    if (is_file($file_name)) {
        unlink($file_name);
    }
    $handler = fopen($file_name, "a+");
    if (!$handler) {
        die();
    }
}

require_once __DIR__ . '/vendor/autoload.php';

use OSMReader\OSMReader;

$handle = fopen("data/ecuador-latest-internal.osm.pbf", "rb");

$osm = new OSMReader($handle);

$file_header = $osm->readFileHeader();


$index = 1046;
$osm->skipToBlock($index);
while ($osm->next()) {
    $reader = $osm->getReader();
    $current = $reader->getPosition();
    $total = $reader->getEofPosition();
    echo $index . "\t-\t" . $current . "/" . $total . "\t\t" . round(($current / $total) * 100, 2) . "%";
    $index++;

    $entities = $osm->getElements();
    makeEntity($entities["type"], $entities["data"]);

}

$end_time = time();
echo "This process took " . ($end_time - $start_time) . " seconds";

function makeEntity($entity, $data)
{
    global $entity_handlers;
    foreach ($data as $datum) {
        fwrite(
            $entity_handlers[$entity . "s"],
            insert_entity($entity, $datum)
        );
        foreach ($datum["tags"] as $tag_key => $tag_value) {
            $tag = array(
                "key" => $tag_key,
                "value" => $tag_value,
            );
            fwrite(
                $entity_handlers[$entity . "_tags"],
                insert_entity_tag($entity, $datum["id"], $tag)
            );
        }

    }

}

function insert_entity($entity, $values)
{
    $insert_data = array(
        "id" => $values["id"],
        "latitude" => $values["latitude"],
        "longitude" => $values["longitude"],
        "changeset_id" => $values["changeset_id"],
        "visible" => $values["visible"],
        "timestamp" => $values["timestamp"],
        "version" => $values["version"],
        "user" => $values["user"],
    );

    if (isset($values["timestamp"])) {
        $insert_data["timestamp"] = str_replace("T", " ", $insert_data["timestamp"]);
        $insert_data["timestamp"] = str_replace("Z", "", $insert_data["timestamp"]);
    }
    if ($entity != "node") {
        unset($insert_data["latitude"]);
        unset($insert_data["longitude"]);
    }

    $escaped_values = array_map('mb_escape', array_values($insert_data));
    $values = "'" . implode("', '", $escaped_values) . "'";
    return "INSERT INTO " . $entity . "s VALUES ($values);\n";
}

function insert_entity_tag($entity, $id, $values)
{
    $insert_data = array(
        $entity . "_id" => $id,
        "k" => $values["key"],
        "v" => $values["value"]
    );

    $escaped_values = array_map('mb_escape', array_values($insert_data));
    $values = "'" . implode("', '", $escaped_values) . "'";
    return "INSERT INTO " . $entity . "_tags VALUES ($values);\n";
}


//function insert_entity_node($entity, $entity_id, $node_id, $sequence)
//{
//    $insert_data = array(
//        $entity . "_id" => $entity_id,
//        "node_id" => $node_id,
//        "sequence" => $sequence
//    );
//
//    $escaped_values = array_map('mb_escape', array_values($insert_data));
//    $values = "'" . implode("', '", $escaped_values) . "'";
//    return "INSERT INTO " . $entity . "_nodes VALUES ($values);\n";
//
//}
//
//function insert_entity_member($entity, $entity_id, $values, $sequence)
//{
//    $insert_data = array(
//        $entity . "_id" => $entity_id,
//        "member_type" => $values["type"],
//        "member_id" => $values["ref"],
//        "member_role" => $values["role"],
//        "sequence" => $sequence
//    );
//
//    $escaped_values = array_map('mb_escape', array_values($insert_data));
//    $values = "'" . implode("', '", $escaped_values) . "'";
//    return "INSERT INTO " . $entity . "_membars VALUES ($values);\n";
//
//}