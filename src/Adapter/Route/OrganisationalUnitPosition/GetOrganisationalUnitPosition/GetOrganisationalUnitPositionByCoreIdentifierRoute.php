<?php

namespace FluxIliasRestApi\Adapter\Route\OrganisationalUnitPosition\GetOrganisationalUnitPosition;

use FluxIliasRestApi\Adapter\Api\IliasRestApi;
use FluxIliasRestApi\Adapter\Body\JsonBodyDto;
use FluxIliasRestApi\Adapter\Body\TextBodyDto;
use FluxIliasRestApi\Adapter\Body\Type\DefaultBodyType;
use FluxIliasRestApi\Adapter\Method\DefaultMethod;
use FluxIliasRestApi\Adapter\Method\Method;
use FluxIliasRestApi\Adapter\OrganisationalUnit\OrganisationalUnitDto;
use FluxIliasRestApi\Adapter\OrganisationalUnitPosition\OrganisationalUnitPositionCoreIdentifier;
use FluxIliasRestApi\Adapter\Route\Documentation\RouteDocumentationDto;
use FluxIliasRestApi\Adapter\Route\Documentation\RouteParamDocumentationDto;
use FluxIliasRestApi\Adapter\Route\Documentation\RouteResponseDocumentationDto;
use FluxIliasRestApi\Adapter\Route\Route;
use FluxIliasRestApi\Adapter\Server\ServerRequestDto;
use FluxIliasRestApi\Adapter\Server\ServerResponseDto;
use FluxIliasRestApi\Adapter\Status\DefaultStatus;

class GetOrganisationalUnitPositionByCoreIdentifierRoute implements Route
{

    private function __construct(
        private readonly IliasRestApi $ilias_rest_api
    ) {

    }


    public static function new(
        IliasRestApi $ilias_rest_api
    ) : static {
        return new static(
            $ilias_rest_api
        );
    }


    public function getDocumentation() : ?RouteDocumentationDto
    {
        return RouteDocumentationDto::new(
            $this->getRoute(),
            $this->getMethod(),
            "Get organisational unit position by core identifier",
            null,
            [
                RouteParamDocumentationDto::new(
                    "core_identifier",
                    OrganisationalUnitPositionCoreIdentifier::class,
                    "Organisational unit position core identifier"
                )
            ],
            null,
            null,
            [
                RouteResponseDocumentationDto::new(
                    DefaultBodyType::JSON,
                    null,
                    OrganisationalUnitDto::class,
                    "Organisational unit position"
                ),
                RouteResponseDocumentationDto::new(
                    DefaultBodyType::TEXT,
                    DefaultStatus::_404,
                    null,
                    "Organisational unit position not found"
                )
            ]
        );
    }


    public function getMethod() : Method
    {
        return DefaultMethod::GET;
    }


    public function getRoute() : string
    {
        return "/organisational-unit-position/by-core-identifier/{core_identifier}";
    }


    public function handle(ServerRequestDto $request) : ?ServerResponseDto
    {
        $organisational_unit_position = $this->ilias_rest_api->getOrganisationalUnitPositionByCoreIdentifier(
            OrganisationalUnitPositionCoreIdentifier::from($request->getParam(
                "core_identifier"
            ))
        );

        if ($organisational_unit_position !== null) {
            return ServerResponseDto::new(
                JsonBodyDto::new(
                    $organisational_unit_position
                )
            );
        } else {
            return ServerResponseDto::new(
                TextBodyDto::new(
                    "Organisational unit position not found"
                ),
                DefaultStatus::_404
            );
        }
    }
}
