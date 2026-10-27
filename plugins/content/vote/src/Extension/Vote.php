<?php

namespace Aspectox\LaraJoom\Plugins\Content\Vote\Extension;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Vote
{
    public function getName(): string
    {
        return 'content.vote';
    }

    public function getParams(): array
    {
        return [
            'position' => 'top',      // top | bottom
            'show_total_votes' => 0,  // 0 = ocultar, 1 = mostrar
        ];
    }

    /**
     * Inserta el widget de votación (estrellas) en el artículo.
     */
    public function onContentPrepare(string $context, &$article, $params = null): void
    {
        if (!is_object($article) || empty($article->id) || !Schema::hasTable('larajoom_content_votes')) {
            return;
        }

        $position = (string) ($params['position'] ?? 'top');
        $showTotal = (bool) ($params['show_total_votes'] ?? false);

        $avg = DB::table('larajoom_content_votes')
            ->where('content_id', $article->id)
            ->avg('rating');

        $count = DB::table('larajoom_content_votes')
            ->where('content_id', $article->id)
            ->count();

        $avg = round((float) $avg, 1);
        $userId = Auth::guard('ax-larajoom')->id();
        $voted = $userId ? DB::table('larajoom_content_votes')
            ->where('content_id', $article->id)
            ->where('user_id', $userId)
            ->exists() : false;

        $widget = $this->render($article->id, $avg, $count, $showTotal, $voted);
        $target = ($position === 'top') ? 'introtext' : 'fulltext';

        if (isset($article->{$target})) {
            $article->{$target} = $widget . $article->{$target};
        } elseif (isset($article->introtext)) {
            $article->introtext = $widget . $article->introtext;
        }
    }

    /**
     * Registra un voto (POST /vote/{id}).
     */
    public function store(Request $request, int $id)
    {
        if (!Schema::hasTable('larajoom_content_votes')) {
            return response()->json(['error' => 'Tabla de votos no disponible'], 500);
        }

        $rating = (int) $request->input('rating');
        if ($rating < 1 || $rating > 5) {
            return response()->json(['error' => 'Voto inválido'], 422);
        }

        $userId = Auth::guard('ax-larajoom')->id();

        // Evita doble voto por usuario (o por IP si no hay sesión).
        $existing = DB::table('larajoom_content_votes')
            ->where('content_id', $id)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId);
                if (!$userId) {
                    $q->orWhere('ip', request()->ip());
                }
            })
            ->first();

        if ($existing) {
            DB::table('larajoom_content_votes')
                ->where('id', $existing->id)
                ->update(['rating' => $rating]);
        } else {
            DB::table('larajoom_content_votes')->insert([
                'content_id' => $id,
                'user_id'    => $userId,
                'ip'         => request()->ip(),
                'rating'     => $rating,
            ]);
        }

        $avg = round((float) DB::table('larajoom_content_votes')
            ->where('content_id', $id)->avg('rating'), 1);
        $count = DB::table('larajoom_content_votes')
            ->where('content_id', $id)->count();

        return response()->json(['average' => $avg, 'count' => $count]);
    }

    protected function render(int $contentId, float $avg, int $count, bool $showTotal, bool $voted): string
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            $active = $i <= round($avg) ? ' active' : '';
            $stars .= '<button type="button" class="lj-vote-star' . $active . '" data-value="' . $i . '">★</button>';
        }

        $total = $showTotal ? ' <span class="lj-vote-total">(' . $count . ')</span>' : '';

        return '<div class="lj-vote" data-content-id="' . $contentId . '" data-voted="' . ($voted ? '1' : '0') . '">'
            . '<div class="lj-vote-stars">' . $stars . '</div>'
            . '<span class="lj-vote-avg">' . number_format($avg, 1) . '</span>'
            . $total
            . $this->script()
            . '</div>';
    }

    protected function script(): string
    {
        return '<script>'
            . '(function(){var w=document.querySelectorAll(".lj-vote");w.forEach(function(x){'
            . 'if(x.getAttribute("data-voted")==="1")return;'
            . 'var stars=x.querySelectorAll(".lj-vote-star");var cid=x.getAttribute("data-content-id");'
            . 'stars.forEach(function(s){s.addEventListener("click",function(){'
            . 'fetch("/vote/"+cid,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded","X-CSRF-TOKEN":document.querySelector(\'meta[name="csrf-token"]\')?document.querySelector(\'meta[name="csrf-token"]\').getAttribute("content"):""},'
            . 'body:"rating="+s.getAttribute("data-value")}).then(function(r){return r.json()}).then(function(d){'
            . 'x.querySelector(".lj-vote-avg").textContent=d.average.toFixed(1);'
            . 'if(d.count!==undefined){var t=x.querySelector(".lj-vote-total");if(t)t.textContent="("+d.count+")";}})'
            . '});});});})();'
            . '</script>';
    }
}
