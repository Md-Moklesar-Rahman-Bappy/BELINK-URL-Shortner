@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@inject('settings', 'Common\Settings\Settings')

@section('body')
    <h1>{{$meta->getTitle()}}</h1>
    <p>{{$meta->getDescription()}}</p>

    <ul>
        @foreach($meta->getData('linkGroup')->links as $link)
            <li>
                <a href="{{$link->short_url}}" target="_blank">
                    <div class="long-url">
                        <img class="favicon-img" src="{{$link->image}}" alt="">
                        <span>{{$link->long_url}}</span>
                    </div>
                    <div class="short-url">{{$link->short_url}}</div>
                    @if($link->description)
                        <p class="link-description">{{$link->description}}</p>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
@endsection
