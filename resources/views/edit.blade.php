@extends('layouts.app')

@section('javascript')
<script src="/js/confirm.js"></script>

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between form-r">
        メモ編集
        <form id="delete-form" action="{{ route('destroy')}}" method="POST" class="icon-01">
            @csrf
            <input type="hidden" name="memo_id" value="{{$edit_memo[0]['id']}}" />
            <i class="fas fa-trash mr-3" onclick="deleteHandle(event);"></i>
        </form>
    </div>
    <form class="card-body my-card-body" action="{{ route('update')}}" method="POST">
        @csrf
        <!-- hiddenで見えないようにして、IDをわたす -->
        <input type="hidden" name="memo_id" value="{{ $edit_memo[0]['id'] }}" />
        <div class="form-group">
            <textarea class="form-control" name="content" rows="3" placeholder="ここにメモを入力">{{ $edit_memo[0]['content'] }}</textarea>
        </div>
        @error('content')
        <div class="alert alert-danger">メモ内容を入力してください！</div>
        @enderror 
        @foreach($tags as $t)
        <div class="form-check form-check-inline mb-3">
            <!-- ３項演算子 -->
            <!-- もし$include_tagsにループで回っているタグのidが含まれていればチェック in_arrayは配列に含まれている場合という構文 -->
            <input class="form-check-input" type="checkbox" name="tags[]" id="{{ $t['id'] }}" value="{{ $t['id'] }}" {{ in_array($t['id'], $include_tags) ? 'checked' : '' }}>
            <label class="form-check-label" for="{{ $t['id'] }}">{{ $t['name']}}</label>
        </div>
        @endforeach
        <div class="mb3">
            <input type="text" class="form-control w-50 mb-3" name="new_tag" placeholder="新しいタグを入力" />
            <button type="submit" class="btn btn-primary">更新</button>
        </div>
    </form>
</div>
@endsection
