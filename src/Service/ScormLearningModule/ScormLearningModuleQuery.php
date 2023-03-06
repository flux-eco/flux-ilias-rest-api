<?php

namespace FluxIliasRestApi\Service\ScormLearningModule;

use FluxIliasRestApi\Adapter\ScormLearningModule\ScormLearningModuleDiffDto;
use FluxIliasRestApi\Adapter\ScormLearningModule\ScormLearningModuleDto;
use FluxIliasRestApi\Adapter\ScormLearningModule\ScormLearningModuleType;
use FluxIliasRestApi\Service\Object\CustomInternalObjectType;
use FluxIliasRestApi\Service\Object\DefaultInternalObjectType;
use ilDBConstants;
use ilObjSAHSLearningModule;
use ilObjSCORM2004LearningModule;
use ilObjSCORMLearningModule;
use LogicException;

trait ScormLearningModuleQuery
{

    private function getIliasScormLearningModule(int $id, ?int $ref_id = null) : ?ilObjSCORMLearningModule
    {
        $class = ilObjSAHSLearningModule::_lookupSubType($id) === InternalScormLearningModuleType::SCORM_2004->value ? ilObjSCORM2004LearningModule::class : ilObjSCORMLearningModule::class;

        if ($ref_id !== null) {
            return new $class($ref_id, true);
        } else {
            return new $class($id, false);
        }
    }


    private function getScormLearningModuleQuery(?int $id = null, ?string $import_id = null, ?int $ref_id = null, ?bool $in_trash = null) : string
    {
        $wheres = [
            "object_data.type=" . $this->ilias_database->quote(DefaultInternalObjectType::SAHS->value, ilDBConstants::T_TEXT)
        ];

        if ($id !== null) {
            $wheres[] = "object_data.obj_id=" . $this->ilias_database->quote($id, ilDBConstants::T_INTEGER);
        }

        if ($import_id !== null) {
            $wheres[] = "object_data.import_id=" . $this->ilias_database->quote($import_id, ilDBConstants::T_TEXT);
        }

        if ($ref_id !== null) {
            $wheres[] = "object_reference.ref_id=" . $this->ilias_database->quote($ref_id, ilDBConstants::T_INTEGER);
        }

        if ($in_trash !== null) {
            $wheres[] = "object_reference.deleted IS" . ($in_trash ? " NOT" : "") . " NULL";
        }

        return "SELECT object_data.*,object_reference.ref_id,object_reference.deleted,didactic_tpl_objs.tpl_id,sahs_lm.*,object_data_parent.obj_id AS parent_obj_id,object_reference_parent.ref_id AS parent_ref_id,object_data_parent.import_id AS parent_import_id
FROM object_data
LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id
LEFT JOIN didactic_tpl_objs ON object_data.obj_id=didactic_tpl_objs.obj_id
LEFT JOIN sahs_lm ON object_data.obj_id=sahs_lm.id
LEFT JOIN tree ON object_reference.ref_id=tree.child
LEFT JOIN object_reference AS object_reference_parent ON tree.parent=object_reference_parent.ref_id
LEFT JOIN object_data AS object_data_parent ON object_reference_parent.obj_id=object_data_parent.obj_id
WHERE " . implode(" AND ", $wheres) . "
GROUP BY object_data.obj_id
ORDER BY object_data.title ASC,object_data.create_date ASC,object_reference.ref_id ASC";
    }


    private function mapScormLearningModuleDiff(ScormLearningModuleDiffDto $diff, ilObjSCORMLearningModule $ilias_scorm_learning_module) : void
    {
        if ($diff->import_id !== null) {
            $ilias_scorm_learning_module->setImportId($diff->import_id);
        }

        if ($diff->type !== null) {
            $ilias_scorm_learning_module->setSubType(ScormLearningModuleTypeMapping::mapExternalToInternal($diff->type)->value);
        }

        if ($diff->authoring_mode !== null) {
            if ($diff->authoring_mode && $ilias_scorm_learning_module->getSubType() !== InternalScormLearningModuleType::SCORM_2004->value) {
                throw new LogicException("Can only enable authoring mode for type " . ScormLearningModuleType::SCORM_2004->value);
            }

            $ilias_scorm_learning_module->setEditable($diff->authoring_mode);
        }

        if ($diff->sequencing_expert_mode !== null) {
            $ilias_scorm_learning_module->setSequencingExpertMode($diff->sequencing_expert_mode);
        }

        if ($diff->online !== null) {
            $ilias_scorm_learning_module->setOfflineStatus(!$diff->online);
        }

        if ($diff->title !== null) {
            $ilias_scorm_learning_module->setTitle($diff->title);
        }

        if ($diff->description !== null) {
            $ilias_scorm_learning_module->setDescription($diff->description);
        }

        if ($diff->didactic_template_id !== null) {
            $ilias_scorm_learning_module->applyDidacticTemplate($diff->didactic_template_id);
        }
    }


    private function mapScormLearningModuleDto(array $scorm_learning_module) : ScormLearningModuleDto
    {
        $object_type = ($object_type = $scorm_learning_module["type"] ?: null) !== null ? CustomInternalObjectType::factory(
            $object_type
        ) : null;

        return ScormLearningModuleDto::new(
            $scorm_learning_module["obj_id"] ?: null,
            $scorm_learning_module["import_id"] ?: null,
            $scorm_learning_module["ref_id"] ?: null,
            strtotime($scorm_learning_module["create_date"] ?? null) ?: null,
            strtotime($scorm_learning_module["last_update"] ?? null) ?: null,
            $scorm_learning_module["parent_obj_id"] ?: null,
            $scorm_learning_module["parent_import_id"] ?: null,
            $scorm_learning_module["parent_ref_id"] ?: null,
            $this->getObjectUrl(
                $scorm_learning_module["ref_id"] ?: null,
                $object_type
            ),
            $this->getObjectIconUrl(
                $scorm_learning_module["obj_id"] ?: null,
                $object_type
            ),
            $scorm_learning_module["title"] ?? "",
            $scorm_learning_module["description"] ?? "",
            ($type = $scorm_learning_module["c_type"] ?: null) !== null ? ScormLearningModuleTypeMapping::mapInternalToExternal(InternalScormLearningModuleType::from($type)) : null,
            $scorm_learning_module["module_version"] ?: null,
            !($scorm_learning_module["offline"] ?? null),
            $scorm_learning_module["editable"] ?? false,
            $scorm_learning_module["seq_exp_mode"] ?? false,
            $scorm_learning_module["tpl_id"] ?: null,
            ($scorm_learning_module["deleted"] ?? null) !== null
        );
    }


    private function newIliasScormLearningModule(?ScormLearningModuleType $type = null) : ilObjSCORMLearningModule
    {
        if ($type === ScormLearningModuleType::SCORM_2004) {
            return new ilObjSCORM2004LearningModule();
        } else {
            return new ilObjSCORMLearningModule();
        }
    }
}
