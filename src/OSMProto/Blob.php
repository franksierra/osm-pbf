<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: proto/fileformat.proto

namespace OSMProto;

use Google\Protobuf\Internal\GPBUtil;
use OSMProto\GPBMetadata\Fileformat;

/**
 * Generated from protobuf message <code>OSMReader.Blob</code>
 */
class Blob extends \Google\Protobuf\Internal\Message
{
    /**
     * No compression
     *
     * Generated from protobuf field <code>bytes raw = 1;</code>
     */
    private $raw = '';
    /**
     * When compressed, the uncompressed size
     *
     * Generated from protobuf field <code>int32 raw_size = 2;</code>
     */
    private $raw_size = 0;
    /**
     * Possible compressed versions of the data.
     *
     * Generated from protobuf field <code>bytes zlib_data = 3;</code>
     */
    private $zlib_data = '';
    /**
     * PROPOSED feature for LZMA compressed data. SUPPORT IS NOT REQUIRED.
     *
     * Generated from protobuf field <code>bytes lzma_data = 4;</code>
     */
    private $lzma_data = '';
    /**
     * Formerly used for bzip2 compressed data. Depreciated in 2010.
     *
     * Generated from protobuf field <code>bytes OBSOLETE_bzip2_data = 5 [deprecated = true];</code>
     */
    private $OBSOLETE_bzip2_data = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     * @type string $raw
     *           No compression
     * @type int $raw_size
     *           When compressed, the uncompressed size
     * @type string $zlib_data
     *           Possible compressed versions of the data.
     * @type string $lzma_data
     *           PROPOSED feature for LZMA compressed data. SUPPORT IS NOT REQUIRED.
     * @type string $OBSOLETE_bzip2_data
     *           Formerly used for bzip2 compressed data. Depreciated in 2010.
     * }
     */
    public function __construct($data = NULL)
    {
        Fileformat::initOnce();
        parent::__construct($data);
    }

    /**
     * No compression
     *
     * Generated from protobuf field <code>bytes raw = 1;</code>
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * No compression
     *
     * Generated from protobuf field <code>bytes raw = 1;</code>
     * @param string $var
     * @return $this
     * @throws \Exception
     */
    public function setRaw($var)
    {
        GPBUtil::checkString($var, False);
        $this->raw = $var;

        return $this;
    }

    /**
     * When compressed, the uncompressed size
     *
     * Generated from protobuf field <code>int32 raw_size = 2;</code>
     * @return int
     */
    public function getRawSize()
    {
        return $this->raw_size;
    }

    /**
     * When compressed, the uncompressed size
     *
     * Generated from protobuf field <code>int32 raw_size = 2;</code>
     * @param int $var
     * @return $this
     * @throws \Exception
     */
    public function setRawSize($var)
    {
        GPBUtil::checkInt32($var);
        $this->raw_size = $var;

        return $this;
    }

    /**
     * Possible compressed versions of the data.
     *
     * Generated from protobuf field <code>bytes zlib_data = 3;</code>
     * @return string
     */
    public function getZlibData()
    {
        return $this->zlib_data;
    }

    /**
     * Possible compressed versions of the data.
     *
     * Generated from protobuf field <code>bytes zlib_data = 3;</code>
     * @param string $var
     * @return $this
     * @throws \Exception
     */
    public function setZlibData($var)
    {
        GPBUtil::checkString($var, False);
        $this->zlib_data = $var;

        return $this;
    }

    /**
     * PROPOSED feature for LZMA compressed data. SUPPORT IS NOT REQUIRED.
     *
     * Generated from protobuf field <code>bytes lzma_data = 4;</code>
     * @return string
     */
    public function getLzmaData()
    {
        return $this->lzma_data;
    }

    /**
     * PROPOSED feature for LZMA compressed data. SUPPORT IS NOT REQUIRED.
     *
     * Generated from protobuf field <code>bytes lzma_data = 4;</code>
     * @param string $var
     * @return $this
     * @throws \Exception
     */
    public function setLzmaData($var)
    {
        GPBUtil::checkString($var, False);
        $this->lzma_data = $var;

        return $this;
    }

    /**
     * Formerly used for bzip2 compressed data. Depreciated in 2010.
     *
     * Generated from protobuf field <code>bytes OBSOLETE_bzip2_data = 5 [deprecated = true];</code>
     * @return string
     */
    public function getOBSOLETEBzip2Data()
    {
        return $this->OBSOLETE_bzip2_data;
    }

    /**
     * Formerly used for bzip2 compressed data. Depreciated in 2010.
     *
     * Generated from protobuf field <code>bytes OBSOLETE_bzip2_data = 5 [deprecated = true];</code>
     * @param string $var
     * @return $this
     * @throws \Exception
     */
    public function setOBSOLETEBzip2Data($var)
    {
        GPBUtil::checkString($var, False);
        $this->OBSOLETE_bzip2_data = $var;

        return $this;
    }

}
