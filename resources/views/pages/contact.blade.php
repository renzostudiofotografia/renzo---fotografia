@extends('layouts.app')

@section('title', $site->get('contact_page_header.page_title'))

@section('content')
@foreach($site->pageSections('contact') as $sectionKey)
    @include('pages.sections.'.$sectionKey)
@endforeach

@endsection
