<?php

namespace FluxIliasRestApi\Adapter\Route\Object\LinkObject;

use FluxIliasRestApi\Adapter\Api\IliasRestApi;
use FluxIliasRestApi\Adapter\Body\JsonBodyDto;
use FluxIliasRestApi\Adapter\Body\TextBodyDto;
use FluxIliasRestApi\Adapter\Body\Type\DefaultBodyType;
use FluxIliasRestApi\Adapter\Method\DefaultMethod;
use FluxIliasRestApi\Adapter\Method\Method;
use FluxIliasRestApi\Adapter\Object\ObjectIdDto;
use FluxIliasRestApi\Adapter\Route\Documentation\RouteDocumentationDto;
use FluxIliasRestApi\Adapter\Route\Documentation\RouteParamDocumentationDto;
use FluxIliasRestApi\Adapter\Route\Documentation\RouteResponseDocumentationDto;
use FluxIliasRestApi\Adapter\Route\Route;
use FluxIliasRestApi\Adapter\Server\ServerRequestDto;
use FluxIliasRestApi\Adapter\Server\ServerResponseDto;
use FluxIliasRestApi\Adapter\Status\DefaultStatus;

class LinkObjectByImportIdToRefIdRoute implements Route
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
            "Link object by import id to object by ref id",
            null,
            [
                RouteParamDocumentationDto::new(
                    "import_id",
                    "string",
                    "Object import id"
                ),
                RouteParamDocumentationDto::new(
                    "parent_ref_id",
                    "int",
                    "Parent object ref id"
                )
            ],
            null,
            null,
            [
                RouteResponseDocumentationDto::new(
                    DefaultBodyType::JSON,
                    null,
                    ObjectIdDto::class,
                    "Object ids"
                ),
                RouteResponseDocumentationDto::new(
                    DefaultBodyType::TEXT,
                    DefaultStatus::_404,
                    null,
                    "Object not found"
                )
            ]
        );
    }


    public function getMethod() : Method
    {
        return DefaultMethod::POST;
    }


    public function getRoute() : string
    {
        return "/object/by-import-id/{import_id}/link/to-ref-id/{parent_ref_id}";
    }


    public function handle(ServerRequestDto $request) : ?ServerResponseDto
    {
        $id = $this->ilias_rest_api->linkObjectByImportIdToRefId(
            $request->getParam(
                "import_id"
            ),
            $request->getParam(
                "parent_ref_id"
            )
        );

        if ($id !== null) {
            return ServerResponseDto::new(
                JsonBodyDto::new(
                    $id
                )
            );
        } else {
            return ServerResponseDto::new(
                TextBodyDto::new(
                    "Object not found"
                ),
                DefaultStatus::_404
            );
        }
    }
}
