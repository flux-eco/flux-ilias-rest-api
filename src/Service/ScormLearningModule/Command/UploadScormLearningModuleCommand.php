<?php

namespace FluxIliasRestApi\Service\ScormLearningModule\Command;

use FluxIliasBaseApi\Adapter\Object\ObjectIdDto;
use FluxIliasBaseApi\Adapter\ScormLearningModule\ScormLearningModuleDto;
use FluxIliasRestApi\Service\ScormLearningModule\Port\ScormLearningModuleService;
use FluxIliasRestApi\Service\ScormLearningModule\ScormLearningModuleQuery;
use ilObjSCORM2004LearningModule;
use ilUtil;

class UploadScormLearningModuleCommand
{

    use ScormLearningModuleQuery;

    private function __construct(
        private readonly ScormLearningModuleService $scorm_learning_module_service
    ) {

    }


    public static function new(
        ScormLearningModuleService $scorm_learning_module_service
    ) : static {
        return new static(
            $scorm_learning_module_service
        );
    }


    public function uploadScormLearningModuleById(int $id, string $file) : ?ObjectIdDto
    {
        return $this->uploadScormLearningModule(
            $this->scorm_learning_module_service->getScormLearningModuleById(
                $id,
                false
            ),
            $file
        );
    }


    public function uploadScormLearningModuleByImportId(string $import_id, string $file) : ?ObjectIdDto
    {
        return $this->uploadScormLearningModule(
            $this->scorm_learning_module_service->getScormLearningModuleByImportId(
                $import_id,
                false
            ),
            $file
        );
    }


    public function uploadScormLearningModuleByRefId(int $ref_id, string $file) : ?ObjectIdDto
    {
        return $this->uploadScormLearningModule(
            $this->scorm_learning_module_service->getScormLearningModuleByRefId(
                $ref_id,
                false
            ),
            $file
        );
    }


    private function uploadScormLearningModule(?ScormLearningModuleDto $scorm_learning_module, string $file) : ?ObjectIdDto
    {
        if ($scorm_learning_module === null) {
            return null;
        }

        $ilias_scorm_learning_module = $this->getIliasScormLearningModule(
            $scorm_learning_module->id,
            $scorm_learning_module->ref_id
        );
        if ($ilias_scorm_learning_module === null) {
            return null;
        }

        $ilias_scorm_learning_module->createDataDirectory();

        $new_version = $scorm_learning_module->version;
        if ($new_version >= 1) {
            $new_version = $new_version + 1;
        } else {
            $new_version = 1;
        }

        $file_name = "upload_" . $new_version . ".zip";
        $file_path = $ilias_scorm_learning_module->getDataDirectory() . "/" . $file_name;

        ilUtil::moveUploadedFile(
            $file,
            $file_name,
            $file_path
        );
        ilUtil::unzip($file_path);
        ilUtil::renameExecutables($ilias_scorm_learning_module->getDataDirectory());

        if ($new_version === 1) {
            if ($ilias_scorm_learning_module instanceof ilObjSCORM2004LearningModule) {
                $ilias_scorm_learning_module->setImportSequencing($ilias_scorm_learning_module->getSequencingExpertMode());
            }
            $ilias_scorm_learning_module->readObject();
            $ilias_scorm_learning_module->setLearningProgressSettingsAtUpload();
        }

        $ilias_scorm_learning_module->setModuleVersion($new_version);
        $ilias_scorm_learning_module->update();

        return ObjectIdDto::new(
            $scorm_learning_module->id,
            $scorm_learning_module->import_id,
            $scorm_learning_module->ref_id
        );
    }
}
