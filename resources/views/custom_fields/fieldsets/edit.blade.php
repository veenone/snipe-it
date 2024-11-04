@extends('layouts/edit-form', [
    'createText' => trans('admin/custom_fields/general.create_fieldset') ,
    'updateText' => trans('admin/custom_fields/general.update_fieldset'),
    'helpText' => trans('admin/custom_fields/general.about_fieldsets_text'),
    'helpPosition' => 'right',
    'formAction' => (isset($item->id)) ? route('fieldsets.update', ['fieldset' => $item->id, 'field_category' => 0]) : route('fieldsets.store',['field_category' => 0]),
])

@section('content')
    @parent    
@stop

@section('inputFields')
@include ('partials.forms.edit.name', ['translated_name' => trans('general.name')])
@stop


