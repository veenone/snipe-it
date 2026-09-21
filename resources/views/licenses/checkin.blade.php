@extends('layouts/default')

{{-- Page title --}}
@section('title')
     {{ trans('admin/licenses/general.checkin') }}
@parent
@stop


@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-8">
            <form class="form-horizontal" method="post" action="{{ route('licenses.checkin.save', ['licenseId'=>$licenseSeat->id, 'backTo'=>$backto] ) }}" autocomplete="off">
                {{csrf_field()}}

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ $licenseSeat->license->name }}</h2>
                    </div>
                    <div class="box-body">

            <!-- license name -->
            <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('general.name') }}</label>
                <div class="col-md-8">
                    <p class="form-control-static">{{ $licenseSeat->license->name }}</p>
                </div>
            </div>

            @if ($licenseSeat->license->company)
                <!-- accessory name -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{ trans('general.company') }}</label>
                    <div class="col-md-6">
                        <p class="form-control-static">{!! $licenseSeat->license->company->present()->formattedNameLink  !!}</p>
                    </div>
                </div>
            @endif


            @if ($licenseSeat->license->category)
                <!-- category name -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">{{ trans('general.category') }}</label>
                    <div class="col-md-6">
                        <p class="form-control-static">{!! $licenseSeat->license->category->present()->formattedNameLink  !!}</p>
                    </div>
                </div>
            @endif

            <!-- Serial -->
            @if ($licenseSeat->license->serial)
                @can('viewKeys', $licenseSeat->license)
                    <x-form.static :label="trans('admin/licenses/form.license_key')">
                        <x-copy-to-clipboard copy_what="license_key">
                            <code>{!! nl2br(e($licenseSeat->license->serial)) !!}</code>
                        </x-copy-to-clipboard>
                    </x-form.static>
                @endcan
            @endif

            <!-- Note -->
            <div class="form-group {{ $errors->has('notes') ? 'error' : '' }}">
                <label for="note" class="col-md-3 control-label">{{ trans('general.checkin_note') }}</label>
                <div class="col-md-8">
                    <textarea class="form-control" id="notes" name="notes" rows="5"></textarea>
                    <x-form.error name="notes" />
                </div>
            </div>
                        <x-redirect_submit_options
                                index_route="licenses.index"
                                :button_label="trans('general.checkin')"
                                :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.licenses')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.license')]),
                                'target' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.user')]),
                               ]"
                        />
                    </div> <!-- /.box-->
            </form>
        </div> <!-- /.col-md-7-->
    </div>


@stop
