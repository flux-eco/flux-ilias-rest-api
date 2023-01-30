<?php

namespace FluxIliasRestApi\Service\File\Command;

use FluxIliasBaseApi\Adapter\File\FileDto;
use FluxIliasRestApi\Service\File\FileQuery;
use FluxIliasRestApi\Service\Object\ObjectQuery;
use ilDBInterface;
use LogicException;

class GetFileCommand
{

    use FileQuery;
    use ObjectQuery;

    private function __construct(
        private readonly ilDBInterface $ilias_database
    ) {

    }


    public static function new(
        ilDBInterface $ilias_database
    ) : static {
        return new static(
            $ilias_database
        );
    }


    public function getFileById(int $id, ?bool $in_trash = null) : ?FileDto
    {
        $file = null;
        while (($file_ = $this->ilias_database->fetchAssoc($result ??= $this->ilias_database->query($this->getFileQuery(
                $id,
                null,
                null,
                $in_trash
            )))) !== null) {
            if ($file !== null) {
                throw new LogicException("Multiple files found with the id " . $id);
            }
            $file = $this->mapFileDto(
                $file_
            );
        }

        return $file;
    }


    public function getFileByImportId(string $import_id, ?bool $in_trash = null) : ?FileDto
    {
        $file = null;
        while (($file_ = $this->ilias_database->fetchAssoc($result ??= $this->ilias_database->query($this->getFileQuery(
                null,
                $import_id,
                null,
                $in_trash
            )))) !== null) {
            if ($file !== null) {
                throw new LogicException("Multiple files found with the import id " . $import_id);
            }
            $file = $this->mapFileDto(
                $file_
            );
        }

        return $file;
    }


    public function getFileByRefId(int $ref_id, ?bool $in_trash = null) : ?FileDto
    {
        $file = null;
        while (($file_ = $this->ilias_database->fetchAssoc($result ??= $this->ilias_database->query($this->getFileQuery(
                null,
                null,
                $ref_id,
                $in_trash
            )))) !== null) {
            if ($file !== null) {
                throw new LogicException("Multiple files found with the ref id " . $ref_id);
            }
            $file = $this->mapFileDto(
                $file_
            );
        }

        return $file;
    }
}
