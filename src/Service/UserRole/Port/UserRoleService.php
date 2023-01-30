<?php

namespace FluxIliasRestApi\Service\UserRole\Port;

use FluxIliasBaseApi\Adapter\UserRole\UserRoleDto;
use FluxIliasRestApi\Service\Role\Port\RoleService;
use FluxIliasRestApi\Service\User\Port\UserService;
use FluxIliasRestApi\Service\UserRole\Command\AddUserRoleCommand;
use FluxIliasRestApi\Service\UserRole\Command\GetUserRolesCommand;
use FluxIliasRestApi\Service\UserRole\Command\RemoveUserRoleCommand;
use ilDBInterface;
use ILIAS\DI\RBACServices;

class UserRoleService
{

    private function __construct(
        private readonly ilDBInterface $ilias_database,
        private readonly UserService $user_service,
        private readonly RoleService $role_service,
        private readonly RBACServices $ilias_rbac
    ) {

    }


    public static function new(
        ilDBInterface $ilias_database,
        UserService $user_service,
        RoleService $role_service,
        RBACServices $ilias_rbac
    ) : static {
        return new static(
            $ilias_database,
            $user_service,
            $role_service,
            $ilias_rbac
        );
    }


    public function addUserRoleByIdByRoleId(int $id, int $role_id) : ?UserRoleDto
    {
        return AddUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->addUserRoleByIdByRoleId(
                $id,
                $role_id
            );
    }


    public function addUserRoleByIdByRoleImportId(int $id, string $role_import_id) : ?UserRoleDto
    {
        return AddUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->addUserRoleByIdByRoleImportId(
                $id,
                $role_import_id
            );
    }


    public function addUserRoleByImportIdByRoleId(string $import_id, int $role_id) : ?UserRoleDto
    {
        return AddUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->addUserRoleByImportIdByRoleId(
                $import_id,
                $role_id
            );
    }


    public function addUserRoleByImportIdByRoleImportId(string $import_id, string $role_import_id) : ?UserRoleDto
    {
        return AddUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->addUserRoleByImportIdByRoleImportId(
                $import_id,
                $role_import_id
            );
    }


    /**
     * @return UserRoleDto[]
     */
    public function getUserRoles(?int $user_id = null, ?string $user_import_id = null, ?int $role_id = null, ?string $role_import_id = null) : array
    {
        return GetUserRolesCommand::new(
            $this->ilias_database
        )
            ->getUserRoles(
                $user_id,
                $user_import_id,
                $role_id,
                $role_import_id
            );
    }


    public function removeUserRoleByIdByRoleId(int $id, int $role_id) : ?UserRoleDto
    {
        return RemoveUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->removeUserRoleByIdByRoleId(
                $id,
                $role_id
            );
    }


    public function removeUserRoleByIdByRoleImportId(int $id, string $role_import_id) : ?UserRoleDto
    {
        return RemoveUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->removeUserRoleByIdByRoleImportId(
                $id,
                $role_import_id
            );
    }


    public function removeUserRoleByImportIdByRoleId(string $import_id, int $role_id) : ?UserRoleDto
    {
        return RemoveUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->removeUserRoleByImportIdByRoleId(
                $import_id,
                $role_id
            );
    }


    public function removeUserRoleByImportIdByRoleImportId(string $import_id, string $role_import_id) : ?UserRoleDto
    {
        return RemoveUserRoleCommand::new(
            $this->user_service,
            $this->role_service,
            $this->ilias_rbac
        )
            ->removeUserRoleByImportIdByRoleImportId(
                $import_id,
                $role_import_id
            );
    }
}
