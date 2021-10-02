@extends('common::prerender.base')

<?php /** @var Common\Core\Prerender\MetaTags $meta */ ?>

@inject('settings', 'Common\Settings\Settings')

@section('body')
    <h1>{{$meta->getTitle()}}</h1>
    <p>{{$meta->getDescription()}}</p>

    @if($image = $meta->getData('link.image'))
        <img src="{{$image}}">
    @endif

    @if($page = $meta->getData('link.custom_page'))
        <section>
            @if($meta->getData('link.type') === 'overlay')
                <div class="message">{{$page['message']}}</div>
                @if($btnText = $page['btn_text'])
                    <a class="main-button" href="{{$page['btn_link']}}">{{$btnText}}</a>
                @endif
                <div class="ribbon-wrapper">
                    <div class="ribbon">{{$page['label']}}</div>
                </div>
            @elseif($meta->getData('link.type') === 'page')
                {!! $page['body'] !!}
            @endif
        </section>
    @endif

    <a href="{{$meta->getData('link.long_url')}}">{{__('Go to Link')}}</a>
@endsection
