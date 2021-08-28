<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $memos = Memo::select('memos.*')
            ->where('user_id' , '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at','DESC') //ASC=小さい順
            ->get();

        $tags = Tag::where('user_id', '=', \Auth::id())->whereNull('deleted_at')->orderBy('id', 'DESC')
        ->get();

        return view('create', compact('memos', 'tags'));
    }
    
    public function store(Request $request)
    {
        $posts = $request->all();

        DB::transaction(function() use($posts) {
            //memo_idにMemoテーブルのcontent,ログインuser_idを代入
           $memo_id = Memo::insertGetId(['content' => $posts['content'], 'user_id' => \Auth::id()]);
           //tag_existsにwhereで既存タグあるのか✔、existsメソッドは存在するか調べてくれる
           $tag_exists = Tag::where('user_id' ,'=', \Auth::id())->where('name' ,'=', $posts['new_tag'])
           ->exists();
           //タグの中身が入っている かつ tag_existsが成立していない場合、tag_idにinsert
           if( !empty($posts['new_tag']) && !$tag_exists ){
            $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
            //memo_tagにinsertしてメモとタグを紐付ける
            MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }
            // 既存タグが紐付けられた場合
            foreach($posts['tags'] as $tag){
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag]);
            }
        });


        return redirect(route( 'home' ));
    }

    public function edit($id)
    {
        // エディットページ表示の際にもメモデータとる必要あり、エラーになる
        $memos = Memo::select('memos.*')
            ->where('user_id' , '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('updated_at','DESC') //ASC=小さい順
            ->get();


        $edit_memo = Memo::select('memos.*', 'tags.id AS tag_id')
        ->leftjoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
        ->leftjoin('tags', 'memo_tags.tag_id', '=', 'tags.id')
        ->where('memos.user_id', '=', \Auth::id())
        ->where('memos.id', '=', $id)
        ->whereNull('memos.deleted_at')
        ->get();

        $include_tags = [];
        foreach($edit_memo as $memo){
            array_push($include_tags, $memo['tag_id']);
        }

        $tags = Tag::where('user_id', '=', \Auth::id())->whereNull('deleted_at')->orderBy('id', 'DESC')
        ->get();
        
        return view('edit', compact('memos', 'edit_memo', 'include_tags', 'tags'));
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        Memo::where('id', $posts['memo_id'])->update(['content' => $posts['content']]);

        return redirect(route( 'home' ));
    }
    
    public function destroy(Request $request)
    {
        $posts = $request->all();
        
        Memo::where('id', $posts['memo_id'])->update(['deleted_at' => date("Y-m-d H:i:s",time())]);

        return redirect(route( 'home' ));
    }
}
