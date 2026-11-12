@extends('layouts.app')

@section('title', $site->get('home_hero.page_title'))

@section('content')
@foreach($site->pageSections('home') as $sectionKey)
    @include('pages.sections.'.$sectionKey)
@endforeach

@endsection
