<?php

namespace Happytodev\BlogrDocs\Extensions;

use League\CommonMark\Extension\Embed\EmbedAdapterInterface;

class MediaEmbedAdapter implements EmbedAdapterInterface
{
    private array $enabledPlatforms;

    public function __construct(array $enabledPlatforms = [])
    {
        $this->enabledPlatforms = $enabledPlatforms;
    }

    public function updateEmbeds(array $embeds): void
    {
        foreach ($embeds as $embed) {
            $code = $this->resolveEmbedCode($embed->getUrl());
            if ($code !== null) {
                $embed->setEmbedCode($code);
            }
        }
    }

    private function resolveEmbedCode(string $url): ?string
    {
        return $this->resolveYouTube($url)
            ?? $this->resolveVimeo($url)
            ?? $this->resolveDailymotion($url)
            ?? $this->resolveSpotify($url)
            ?? $this->resolveSoundCloud($url)
            ?? $this->resolveDeezer($url)
            ?? $this->resolveApplePodcasts($url);
    }

    private function isEnabled(string $platform): bool
    {
        if (empty($this->enabledPlatforms)) {
            return true;
        }

        return $this->enabledPlatforms[$platform] ?? true;
    }

    private function resolveYouTube(string $url): ?string
    {
        if (! $this->isEnabled('youtube')) {
            return null;
        }

        if (preg_match(
            '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            $url,
            $matches
        )) {
            return $this->iframe(
                'https://www.youtube-nocookie.com/embed/'.$matches[1],
                'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen'
            );
        }

        return null;
    }

    private function resolveVimeo(string $url): ?string
    {
        if (! $this->isEnabled('vimeo')) {
            return null;
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $this->iframe(
                'https://player.vimeo.com/video/'.$matches[1],
                'allow="autoplay; fullscreen; picture-in-picture" allowfullscreen'
            );
        }

        return null;
    }

    private function resolveDailymotion(string $url): ?string
    {
        if (! $this->isEnabled('dailymotion')) {
            return null;
        }

        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $this->iframe(
                'https://www.dailymotion.com/embed/video/'.$matches[1],
                'allow="autoplay; fullscreen; picture-in-picture" allowfullscreen'
            );
        }

        return null;
    }

    private function resolveSpotify(string $url): ?string
    {
        if (! $this->isEnabled('spotify')) {
            return null;
        }

        // open.spotify.com/track/ID
        if (preg_match('/open\.spotify\.com\/track\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $this->iframe(
                'https://open.spotify.com/embed/track/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        // open.spotify.com/episode/ID
        if (preg_match('/open\.spotify\.com\/episode\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $this->iframe(
                'https://open.spotify.com/embed/episode/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        // open.spotify.com/playlist/ID
        if (preg_match('/open\.spotify\.com\/playlist\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $this->iframe(
                'https://open.spotify.com/embed/playlist/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        // open.spotify.com/album/ID
        if (preg_match('/open\.spotify\.com\/album\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $this->iframe(
                'https://open.spotify.com/embed/album/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        // open.spotify.com/show/ID
        if (preg_match('/open\.spotify\.com\/show\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $this->iframe(
                'https://open.spotify.com/embed/show/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        return null;
    }

    private function resolveSoundCloud(string $url): ?string
    {
        if (! $this->isEnabled('soundcloud')) {
            return null;
        }

        if (preg_match('/soundcloud\.com\/([a-zA-Z0-9_-]+\/[a-zA-Z0-9_-]+)/', $url, $matches)) {
            $trackPath = rawurlencode($matches[1]);

            return $this->iframe(
                'https://w.soundcloud.com/player/?url=https://soundcloud.com/'.$trackPath.'&color=%2300aabb&auto_play=false&hide_related=false',
                'allow="autoplay"'
            );
        }

        return null;
    }

    private function resolveDeezer(string $url): ?string
    {
        if (! $this->isEnabled('deezer')) {
            return null;
        }

        // deezer.com/track/ID
        if (preg_match('/deezer\.com\/(?:[a-z]{2}\/)?track\/(\d+)/', $url, $matches)) {
            return $this->iframe(
                'https://widget.deezer.com/widget/auto/track/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        // deezer.com/episode/ID
        if (preg_match('/deezer\.com\/(?:[a-z]{2}\/)?episode\/(\d+)/', $url, $matches)) {
            return $this->iframe(
                'https://widget.deezer.com/widget/auto/episode/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        // deezer.com/playlist/ID
        if (preg_match('/deezer\.com\/(?:[a-z]{2}\/)?playlist\/(\d+)/', $url, $matches)) {
            return $this->iframe(
                'https://widget.deezer.com/widget/auto/playlist/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        // deezer.com/album/ID
        if (preg_match('/deezer\.com\/(?:[a-z]{2}\/)?album\/(\d+)/', $url, $matches)) {
            return $this->iframe(
                'https://widget.deezer.com/widget/auto/album/'.$matches[1],
                'allow="autoplay; clipboard-write; encrypted-media; picture-in-picture" allowfullscreen'
            );
        }

        return null;
    }

    private function resolveApplePodcasts(string $url): ?string
    {
        if (! $this->isEnabled('apple_podcasts')) {
            return null;
        }

        // podcasts.apple.com/podcast/id
        if (preg_match('/podcasts\.apple\.com\/(?:[a-z]{2}\/)?podcast\/[^\/]+\/id(\d+)/', $url, $matches)) {
            return $this->iframe(
                'https://embed.podcasts.apple.com/'.$url,
                'allow="autoplay"'
            );
        }

        return null;
    }

    private function iframe(string $src, string $extraAttributes): string
    {
        return sprintf(
            '<div class="media-embed aspect-video w-full rounded-xl overflow-hidden shadow-lg my-8">'
            .'<iframe src="%s" class="w-full h-full" frameborder="0" %s></iframe>'
            .'</div>',
            htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $extraAttributes
        );
    }
}
