<?php

declare(strict_types=1);

namespace App\UI\Http\Rest\Controller\User;

use App\Application\Command\CommandBusInterface;
use App\Application\Command\User\ChangeEmail\ChangeEmailCommand;
use App\Domain\User\Exception\ForbiddenException;
use App\UI\Http\Rest\Controller\CommandController;
use App\UI\Http\Session;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Nelmio\ApiDocBundle\Annotation\Security;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

final class UserChangeEmailController extends CommandController
{
    private Session $session;

    public function __construct(Session $session, CommandBusInterface $commandBus)
    {
        parent::__construct($commandBus);

        $this->session = $session;
    }

    /**
     * @Route(
     *     "/users/{uuid}/email",
     *     name="user_change_email",
     *     methods={"POST"}
     * )
     *
     * @SWG\Response(
     *     response=201,
     *     description="Email changed"
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad request"
     * )
     * @SWG\Response(
     *     response=409,
     *     description="Conflict"
     * )
     * @SWG\Parameter(
     *     name="change-email",
     *     type="object",
     *     in="body",
     *     schema=@SWG\Schema(type="object",
     *         @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="uuid",
     *     type="string",
     *     in="path"
     * )
     *
     * @SWG\Tag(name="User")
     *
     * @Security(name="Bearer")
     *
     * @throws AssertionFailedException
     * @throws Throwable
     */
    public function __invoke(string $uuid, Request $request): JsonResponse
    {
        $this->validateUuid($uuid);

        $email = $request->get('email');

        Assertion::notNull($email, "Email can\'t be null");

        $command = new ChangeEmailCommand($uuid, $email);

        $this->handle($command);

        return new JsonResponse();
    }

    private function validateUuid(string $uuid): void
    {
        if (!$this->session->get()->uuid()->equals(Uuid::fromString($uuid))) {
            throw new ForbiddenException();
        }
    }
}
