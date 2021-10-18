<?php

namespace Fluxlabs\FluxIliasRestApi\Channel\GroupMember;

use Fluxlabs\FluxIliasRestApi\Adapter\Api\GroupMember\GroupMemberDiffDto;
use Fluxlabs\FluxIliasRestApi\Adapter\Api\GroupMember\GroupMemberDto;
use Fluxlabs\FluxIliasRestApi\Channel\Object\InternalObjectType;
use Fluxlabs\FluxIliasRestApi\Channel\ObjectLearningProgress\ObjectLearningProgressMapping;
use ilDBConstants;
use ilLPStatus;
use ilObjGroup;
use LogicException;

trait GroupMemberQuery
{

    private function getGroupMemberQuery(
        ?int $group_id = null,
        ?string $group_import_id = null,
        ?int $group_ref_id = null,
        ?int $user_id = null,
        ?string $user_import_id = null,
        ?bool $member_role = null,
        ?bool $administrator_role = null,
        ?string $learning_progress = null,
        ?bool $tutorial_support = null,
        ?bool $notification = null
    ) : string {
        $wheres = [
            "object_data.type=" . $this->database->quote(InternalObjectType::GRP, ilDBConstants::T_TEXT),
            "object_data_user.type=" . $this->database->quote(InternalObjectType::USR, ilDBConstants::T_TEXT),
            "object_reference.deleted IS NULL"
        ];

        if ($group_id !== null) {
            $wheres[] = "object_data.obj_id=" . $this->database->quote($group_id, ilDBConstants::T_INTEGER);
        }

        if ($group_import_id !== null) {
            $wheres[] = "object_data.import_id=" . $this->database->quote($group_import_id, ilDBConstants::T_TEXT);
        }

        if ($group_ref_id !== null) {
            $wheres[] = "object_reference.ref_id=" . $this->database->quote($group_ref_id, ilDBConstants::T_INTEGER);
        }

        if ($user_id !== null) {
            $wheres[] = "object_data_user.obj_id=" . $this->database->quote($user_id, ilDBConstants::T_INTEGER);
        }

        if ($user_import_id !== null) {
            $wheres[] = "object_data_user.import_id=" . $this->database->quote($user_import_id, ilDBConstants::T_TEXT);
        }

        if ($member_role !== null) {
            $wheres[] = "member=" . $this->database->quote($member_role, ilDBConstants::T_INTEGER);
        }

        if ($administrator_role !== null) {
            $wheres[] = "admin=" . $this->database->quote($administrator_role, ilDBConstants::T_INTEGER);
        }

        if ($learning_progress !== null) {
            $wheres[] = "status=" . $this->database->quote(ObjectLearningProgressMapping::mapExternalToInternal(
                    $learning_progress
                ), ilDBConstants::T_INTEGER);
        }

        if ($tutorial_support !== null) {
            $wheres[] = "contact=" . $this->database->quote($tutorial_support, ilDBConstants::T_INTEGER);
        }

        if ($notification !== null) {
            $wheres[] = "notification=" . $this->database->quote($notification, ilDBConstants::T_INTEGER);
        }

        return "SELECT obj_members.*,object_data.obj_id,object_data.import_id,object_reference.ref_id,object_data_user.obj_id AS usr_id,object_data_user.import_id AS user_import_id,status
FROM obj_members
INNER JOIN object_data ON obj_members.obj_id=object_data.obj_id
LEFT JOIN object_reference ON object_data.obj_id=object_reference.obj_id
INNER JOIN object_data AS object_data_user ON obj_members.usr_id=object_data_user.obj_id
LEFT JOIN ut_lp_marks ON ut_lp_marks.obj_id=object_data.obj_id AND ut_lp_marks.usr_id=object_data_user.obj_id
WHERE " . implode(" AND ", $wheres) . "
ORDER BY object_data.obj_id ASC,object_data_user.obj_id ASC";
    }


    private function mapGroupMemberDiff(GroupMemberDiffDto $diff, int $user_id, ilObjGroup $ilias_group) : void
    {
        $roles = [
            InternalGroupMemberType::ADMINISTRATOR => $diff->isAdministratorRole() !== null ? $diff->isAdministratorRole() : $ilias_group->getMembersObject()->isAdmin($user_id),
            InternalGroupMemberType::MEMBER        => $diff->isMemberRole() !== null ? $diff->isMemberRole() : $ilias_group->getMembersObject()->isMember($user_id)
        ];
        if (empty($roles = array_filter($roles))) {
            throw new LogicException("Group member must have at least one role");
        }
        if (!$ilias_group->getMembersObject()->isAssigned($user_id)) {
            $ilias_group->getMembersObject()->add($user_id, array_key_first($roles));
        }
        $ilias_group->getMembersObject()->updateRoleAssignments($user_id, array_map([
            $ilias_group->getMembersObject(),
            "getAutoGeneratedRoleId"
        ], array_keys($roles)));

        if ($diff->getLearningProgress() !== null) {
            ilLPStatus::writeStatus($ilias_group->getId(), $user_id, ObjectLearningProgressMapping::mapExternalToInternal(
                $diff->getLearningProgress()
            ));
        }

        if ($roles[InternalGroupMemberType::ADMINISTRATOR]) {
            $ilias_group->getMembersObject()->updateContact($user_id, $diff->isTutorialSupport() !== null ? $diff->isTutorialSupport() : $ilias_group->getMembersObject()->isContact($user_id));

            $ilias_group->getMembersObject()
                ->updateNotification($user_id, $diff->isNotification() !== null ? $diff->isNotification() : $ilias_group->getMembersObject()->isNotificationEnabled($user_id));
        } else {
            $ilias_group->getMembersObject()->updateContact($user_id, false);

            $ilias_group->getMembersObject()->updateNotification($user_id, false);
        }
    }


    private function mapGroupMemberDto(array $group_member) : GroupMemberDto
    {
        return GroupMemberDto::new(
            $group_member["obj_id"] ?: null,
            $group_member["import_id"] ?: null,
            $group_member["ref_id"] ?: null,
            $group_member["usr_id"] ?: null,
            $group_member["user_import_id"] ?: null,
            $group_member["member"] ?? false,
            $group_member["admin"] ?? false,
            ObjectLearningProgressMapping::mapInternalToExternal(
                $group_member["status"] ?? null
            ),
            $group_member["contact"] ?? false,
            $group_member["notification"] ?? false
        );
    }
}
