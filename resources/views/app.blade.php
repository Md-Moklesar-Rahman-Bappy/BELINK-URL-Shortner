@extends('common::framework')

@section('progressive-app-tags')
{{--	<link rel="manifest" href="client/manifest.json">--}}
	<meta name="theme-color" content="#1976d2">
@endsection

@section('angular-styles')
    {{--angular styles begin--}}
		<link rel="stylesheet" href="client/styles.fe1490f0418ff0b8839a.css">
	{{--angular styles end--}}
@endsection

@section('angular-scripts')
    {{--angular scripts begin--}}
		<script src="client/runtime-es2015.89f12e82cd201e95d333.js" type="module"></script>
		<script src="client/runtime-es5.89f12e82cd201e95d333.js" nomodule defer></script>
		<script src="client/polyfills-es5.944d98bf4e728b837b7f.js" nomodule defer></script>
		<script src="client/polyfills-es2015.01f6085b6db6d65ce20a.js" type="module"></script>
		<script src="client/main-es2015.3a29c27599a746f259c2.js" type="module"></script>
		<script src="client/main-es5.3a29c27599a746f259c2.js" nomodule defer></script>
	{{--angular scripts end--}}
@endsection

@if($link = Request::route('linkResponse.link'))
	@foreach($link->pixels as $pixel)
		@include("pixels.{$pixel->type}", ['pixel' => $pixel])
	@endforeach
@endif
