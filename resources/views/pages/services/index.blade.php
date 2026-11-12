@extends('layouts.app')

@section('title', $site->get('services_page_header.page_title'))

@section('content')
@foreach($site->pageSections('services') as $sectionKey)
    @include('pages.sections.'.$sectionKey)
@endforeach

@endsection
