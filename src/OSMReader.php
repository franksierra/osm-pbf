<?php
/**
 * Created by PhpStorm.
 * User: sierraf
 * Date: 2/19/2019
 * Time: 5:14 PM
 */

namespace OSMReader;

use PhpBinaryReader\BinaryReader;

class OSMReader
{
    private $reader = null;

    public function __construct($pbfdata)
    {
        $this->reader = new BinaryReader($pbfdata, "big");
    }

    public function readHeader()
    {


        /**
         * OSMHeader
         */
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

        ////////////////////////////////////////////////////

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

        ////////////////////////////////////////////////////

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


//
//
//        $size = $this->reader->readUInt32();
//        $header_data = $this->reader->readFromHandle($size);
//
//        $header = new BlobHeader();
//        $header->mergeFromString($header_data);
//
//        $blob_data = $this->reader->readFromHandle($header->getDatasize());
//        $blob = new Blob();
//        $blob->mergeFromString($blob_data);
//
//        $blob_uncompressed = zlib_decode($blob->getZlibData());
//
//        $header_block = new HeaderBlock();
//        $header_block->mergeFromString($blob_uncompressed);


        return $header;
    }

    public function readBlob($size)
    {

    }

}