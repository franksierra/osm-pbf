<?php
/**
 * Created by PhpStorm.
 * User: sierraf
 * Date: 2/19/2019
 * Time: 5:14 PM
 */

use OSMPBF\BlobHeader;
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
        $size = $this->reader->readUInt32();
        $header_data = $this->reader->readFromHandle($size);

        $header = new BlobHeader($header_data);

        return $header;
    }


}