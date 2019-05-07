<?php
/**
 * Created by PhpStorm.
 * User: Frank
 * Date: 2/19/2019
 * Time: 8:22 PM
 */
$start_time = time();

@mkdir(__DIR__ . "/sql/", 0655, true);
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
//    if (is_file($file_name)) {
//        unlink($file_name);
//    }
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

$replication_url = $file_header->getOsmosisReplicationBaseUrl();

$index = 0;     // DenseNodes
//$index = 1049;  // Ways
//$index = 1157;  // Relation
//$index = 1061;
$osm->skipToBlock($index);
while ($data = $osm->next()) {
    $reader = $osm->getReader();
    $current = $reader->getPosition();
    $total = $reader->getEofPosition();
    echo $index . "\t-\t" . $current . "/" . $total . "\t\t" . round(($current / $total) * 100, 2) . "%";
    $index++;

    $entities = $osm->getElements();
    echo "\t" . $entities["type"] . "\t";
    makeEntity($entities["type"], $entities["data"]);
    $entities = null;
    echo "ok\n";
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
        foreach ($datum["tags"] as $tag) {
            fwrite(
                $entity_handlers[$entity . "_tags"],
                insert_entity_tag($entity, $datum["id"], $tag)
            );
        }

        foreach ($datum["nodes"] as $node) {
            fwrite(
                $entity_handlers[$entity . "_nodes"],
                insert_entity_node($entity, $datum["id"], $node)
            );
        }
        foreach ($datum["relations"] as $relation) {
            fwrite(
                $entity_handlers[$entity . "_members"],
                insert_entity_member($entity, $datum["id"], $relation)
            );
        }
    }
}

function insert_entity($entity, $values)
{
    $insert_data = array();

    $insert_data["id"] = $values["id"];
    if ($entity == "node") {
        $insert_data["latitude"] = $values["latitude"];
        $insert_data["longitude"] = $values["longitude"];
    }
    $insert_data["changeset_id"] = $values["changeset_id"];
    $insert_data["visible"] = $values["visible"];
    $insert_data["timestamp"] = $values["timestamp"];
    $insert_data["version"] = $values["version"];
    $insert_data["uid"] = $values["uid"];
    $insert_data["user"] = $values["user"];

    if (isset($values["timestamp"])) {
        $insert_data["timestamp"] = str_replace("T", " ", $insert_data["timestamp"]);
        $insert_data["timestamp"] = str_replace("Z", "", $insert_data["timestamp"]);
    }

    return format_string($entity, "s", $insert_data);
}

function insert_entity_tag($entity, $id, $values)
{
    $insert_data = array(
        $entity . "_id" => $id,
        "k" => $values["key"],
        "v" => $values["value"]
    );

    return format_string($entity, "_tags", $insert_data);
}


function insert_entity_node($entity, $entity_id, $node)
{
    $insert_data = array(
        $entity . "_id" => $entity_id,
        "node_id" => $node["id"],
        "sequence" => $node["sequence"]
    );

    return format_string($entity, "_nodes", $insert_data);
}

function insert_entity_member($entity, $entity_id, $values)
{
    $insert_data = array(
        $entity . "_id" => $entity_id,
        "member_type" => $values["member_type"],
        "member_id" => $values["member_id"],
        "member_role" => $values["member_role"],
        "sequence" => $values["sequence"]
    );

    return format_string($entity, "_members", $insert_data);
}

function format_string($entity, $sufix, $insert_data, $format = "full_sql")
{
    $table = $entity . $sufix;
    $escaped_values = array_map('escape', array_values($insert_data));

    $formated_string = "";
    switch ($format) {
        case "full_sql":
            $values = "'" . implode("', '", $escaped_values) . "'";
            $keys = implode(", ", array_keys($insert_data));
            $formated_string = "INSERT INTO ($keys) " . $table . " VALUES ($values);";
            break;
        case "sql":
            $values = "'" . implode("', '", $escaped_values) . "'";
            $formated_string = "INSERT INTO " . $table . " VALUES ($values);";
            break;
        case "csv":
            $formated_string = implode("; ", $escaped_values);
            break;
            default:
                $formated_string = "ARSE";
                break;
    }
    return $formated_string . "\n";
}

function escape($string)
{
    return preg_replace('~[\x00\x0A\x0D\x1A\x22\x27\x5C]~u', '\\\$0', $string);
}