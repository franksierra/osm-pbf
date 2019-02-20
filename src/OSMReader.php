<?php
/**
 * Created by PhpStorm.
 * User: sierraf
 * Date: 2/19/2019
 * Time: 5:14 PM
 */

namespace OSMReader;

use OSMProto\Blob;
use OSMProto\BlobHeader;
use OSMProto\HeaderBlock;
use OSMProto\PrimitiveBlock;
use PhpBinaryReader\BinaryReader;

class OSMReader
{
    private $reader = null;

    private $current_primitive = null;

    public function __construct($pbfdata)
    {
        $this->reader = new BinaryReader($pbfdata, "big");
    }

    public function getReader()
    {
        return $this->reader;
    }

    public function readHeader()
    {
        $size = $this->reader->readUInt32();
        $header_data = $this->reader->readFromHandle($size);
        $header = new BlobHeader();
        $header->mergeFromString($header_data);
        $blob_data = $this->reader->readFromHandle($header->getDatasize());
        $blob = new Blob();
        $blob->mergeFromString($blob_data);

        $blob_uncompressed = zlib_decode($blob->getZlibData());

        $header_block = new HeaderBlock();
        $header_block->mergeFromString($blob_uncompressed);

        return $header_block;
    }

    public function readData()
    {
        /**
         * OSMData
         */
        $size = $this->reader->readUInt32();
        $header_data = $this->reader->readFromHandle($size);
        $header = new BlobHeader();
        $header->mergeFromString($header_data);
        $blob_data = $this->reader->readFromHandle($header->getDatasize());
        $blob = new Blob();
        $blob->mergeFromString($blob_data);

        $blob_uncompressed = zlib_decode($blob->getZlibData());

        $primitive_block = new PrimitiveBlock();
        $primitive_block->mergeFromString($blob_uncompressed);

        $current_primitive = $primitive_block;

        return $primitive_block;
    }

    public function next()
    {
        return $this->readData();
    }



}