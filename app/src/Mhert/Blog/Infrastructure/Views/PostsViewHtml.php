<?php

declare(strict_types = 1);

namespace Mhert\Blog\Infrastructure\Views;

use DateTimeInterface;
use Mhert\Blog\Domain\Frontpage\Post\Post;
use Mhert\Blog\Infrastructure\ParsedownMarkdownParser;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

final class PostsViewHtml
{
    /** @var TwigEnvironment */
    private $twigEnvironment;
    /** @var ParsedownMarkdownParser */
    private $markdownParser;

    public function __construct(
        TwigEnvironment $twigEnvironment,
        ParsedownMarkdownParser $markdownParser
    ) {
        $this->twigEnvironment = $twigEnvironment;
        $this->markdownParser = $markdownParser;
    }

    /**
     * @param Post[] $posts
     */
    public function render(iterable $posts): Response
    {
        $page = [
            'posts' => $this->adjustPosts($posts)
        ];

        return new Response(
            $this->twigEnvironment->render('posts.html.twig', ['page' => $page])
        );
    }

    /**
     * @param Post[] $posts
     * @return mixed[]
     */
    private function adjustPosts(iterable $posts): iterable
    {
        foreach ($posts as $post) {
            $result = [];

            $post->print(
                function (
                    UuidInterface $id,
                    DateTimeInterface $created,
                    string $content
                ) use (
                    &$result
                ): void {
                    $result = [
                        'content' => $this->markdownParser->parse($content),
                        'created' => $created->format(DateTimeInterface::ISO8601),
                    ];
                }
            );

            return yield $result;
        }

        return [];
    }
}
