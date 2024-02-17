<?php

declare(strict_types=1);

namespace Haikara\FlatRoute\Route;

use Haikara\FlatRoute\Exception\MethodNotAllowedException;
use Haikara\Verifier\RuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use function is_string;

trait RouteTrait
{
    protected string $pattern;

    /**
     * @var array<string,callable|class-string>
     */
    protected array $actions = [];

    /**
     * @var array<MiddlewareInterface|class-string<MiddlewareInterface>>
     */
    protected array $middlewares = [];

    /**
     * @var RuleInterface[]
     */
    protected array $rules = [];

    protected ?string $routeName = null;

    protected array $args = [];

    /**
     * @param string $method
     * @param callable|class-string $callback
     * @return void
     */
    public function addAction(string $method, callable|string $callback): void
    {
        $this->actions[$method] = $callback;
    }

    /**
     * @param MiddlewareInterface|class-string<MiddlewareInterface> ...$middlewares
     * @return $this
     */
    public function addMiddleware(MiddlewareInterface|string ...$middlewares): static
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * @return array<MiddlewareInterface|class-string<MiddlewareInterface>>
     */
    public function getMiddlewares(): array {
        return $this->middlewares;
    }

    public function rule(string $placeholder, RuleInterface $rules): static {
        $this->rules[$placeholder] = $rules;

        return $this;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setName(string $routeName): static {
        $this->routeName = $routeName;

        return $this;
    }

    public function hasName(): bool {
        return is_string($this->routeName) && $this->routeName !== '';
    }

    public function getName(): string {
        return $this->routeName ?? '';
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function hasMethod(ServerRequestInterface $request): bool
    {
        $method = $request->getMethod();
        return isset($this->actions[$method]);
    }

    /**
     * @param ServerRequestInterface $request
     * @return callable|class-string
     * @throws MethodNotAllowedException
     */
    public function getAction(ServerRequestInterface $request): callable|string
    {
        $method = $request->getMethod();

        return $this->actions[$method]
            ?? throw new MethodNotAllowedException();
    }
}
