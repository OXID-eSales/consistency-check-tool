<?php

namespace OxidEsales\ConsistencyCheck\ImageManager\DataType;

interface ImageDataTypeInterface
{
    public function getFieldName(): string;
    public function getImageName(): string;
	public function getDirectory(): string;
}
