<?php

namespace FluxIliasRestApi\Adapter\Route\OrganisationalUnitStaff\AddOrganisationalUnitStaff;

use FluxIliasRestApi\Adapter\Api\Api;
use FluxRestApi\Body\JsonBodyDto;
use FluxRestApi\Body\TextBodyDto;
use FluxRestApi\Request\RequestDto;
use FluxRestApi\Response\ResponseDto;
use FluxRestApi\Route\Route;
use FluxRestBaseApi\Method\Method;
use FluxRestBaseApi\Status\Status;

class AddOrganisationalUnitStaffByRefIdByUserImportIdRoute implements Route
{

    private Api $api;


    public static function new(Api $api) : /*static*/ self
    {
        $route = new static();

        $route->api = $api;

        return $route;
    }


    public function getDocuRequestBodyTypes() : ?array
    {
        return null;
    }


    public function getDocuRequestQueryParams() : ?array
    {
        return null;
    }


    public function getMethod() : string
    {
        return Method::POST;
    }


    public function getRoute() : string
    {
        return "/organisational-unit/by-ref-id/{ref_id}/add-staff/by-import-id/{user_import_id}/{position_id}";
    }


    public function handle(RequestDto $request) : ?ResponseDto
    {
        $id = $this->api->addOrganisationalUnitStaffByRefIdByUserImportId(
            $request->getParam(
                "ref_id"
            ),
            $request->getParam(
                "user_import_id"
            ),
            $request->getParam(
                "position_id"
            )
        );

        if ($id !== null) {
            return ResponseDto::new(
                JsonBodyDto::new(
                    $id
                )
            );
        } else {
            return ResponseDto::new(
                TextBodyDto::new(
                    "Organisational unit staff not found"
                ),
                Status::_404
            );
        }
    }
}
