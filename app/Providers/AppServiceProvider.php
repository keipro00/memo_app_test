<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Memo;
use App\Models\Tag;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //すべてのメソッドが呼ばれる前に先に呼ばれるメソッド
        view()->composer('*', function($view){
    
        // インスタンス化
        $memo_model = new Memo();

        $memos = $memo_model->getMyMemo();

        $tags = Tag::where('user_id', '=',\Auth::id())
        ->whereNull('deleted_at')
        ->orderBy('updated_at','DESC') //ASC=小さい順
        ->get();

            $view->with('memos', $memos)->with('tags', $tags);
        });
    }
}
