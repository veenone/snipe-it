<?php

namespace App\Http\Controllers;

use App\Models\AssetModel;
use App\Models\CustomField;
use App\Models\CustomFieldset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

/**
 * This controller handles all actions related to Custom Asset Fields for
 * the Snipe-IT Asset Management application.
 *
 * @todo Improve documentation here.
 * @todo Check for raw DB queries and try to convert them to query builder statements
 * @version    v2.0
 * @author [Brady Wetherington] [<uberbrady@gmail.com>]
 */
class CustomFieldsetsController extends Controller
{

    public function index(Request $request) 
    {
        $fieldCategory = $request->input('field_category', 0);
        $custom_fieldsets = CustomFieldset::where('field_category', $fieldCategory)->get();
        if ($custom_fieldsets->isEmpty()) {
            return redirect()->route("fields.index")
                ->with("error", trans('admin/custom_fields/message.fieldset.does_not_exist'));
        }

        return view('fields.index', compact('custom_fieldsets', 'fieldCategory'));
    }

    /**
     * Validates and stores a new custom field.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @param int $id
     * @since [v1.8]
     */
    public function show($id, Request $request) : View | RedirectResponse
    {   
        
        $cfset = CustomFieldset::with('fields')
            ->where('id', '=', $id)               
            ->orderBy('id', 'ASC')->first();
        
        $this->authorize('view', $cfset);
       
        if ($cfset) {
            $custom_fields_list = CustomField::where('field_category', $request->query('field_category'))
            ->pluck('name', 'id')
            ->toArray();

            $custom_fields_list = ['' => 'Add New Field to Fieldset'] +  $custom_fields_list;

            $maxid = 0;
            foreach ($cfset->fields as $field) {
                if ($field->pivot->order > $maxid) {
                    $maxid = $field->pivot->order;
                }
                if (isset($custom_fields_list[$field->id])) {
                    unset($custom_fields_list[$field->id]);
                }
            }
            
            $fieldCategory = $request->query('field_category');    
            return view('custom_fields.fieldsets.view')->with('custom_fieldset', $cfset)->with('maxid', $maxid + 1)->with('custom_fields_list', $custom_fields_list);
        }

        return redirect()->route('fields.index')
            ->with('error', trans('admin/custom_fields/message.fieldset.does_not_exist'));
    }

    /**
     * Returns a view with a form for creating a new custom fieldset.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v1.8]
     */
    public function create(Request $request) : View
    {
        
        $this->authorize('create', CustomField::class);
        $fieldCategory = $request->query('field_category', 0);
     
        if ($fieldCategory == 0) {
            return view('custom_fields.fieldsets.edit')
            ->with('item', new CustomFieldset())
            ->with('field_category', $fieldCategory);
        } else if ($fieldCategory == 1) {
            return view('custom_fields.fieldsets.consumable_edit')
            ->with('item', new CustomFieldset())
            ->with('field_category', $fieldCategory);
        } 
        
    }

    
    /**
     * Returns a view with a form for creating a new custom fieldset for consumables category.
     *
     * @author [A.Rahardianto] [<veenone@gmail.com>]
     * @since [v7.3]
     */
    public function create_consumable_edit() : View
    {
        $this->authorize('create', CustomField::class);

        return view('custom_fields.fieldsets.consumable_edit')->with('item', new CustomFieldset());
    }

    /**
     * Validates and stores a new custom fieldset.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v1.8]
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request) : RedirectResponse
    {
        $this->authorize('create', CustomField::class);

        $fieldset = new CustomFieldset([
                'name' => $request->get('name'),
                'field_category' => $request->get('field_category'),
                'created_by' => auth()->id(),
        ]);

        $validator = Validator::make($request->all(), $fieldset->rules);

        if ($validator->passes()) {
            $fieldset->save();

            // Sync fieldset with auto_add_to_fieldsets
            $fields = CustomField::select('id')->where('auto_add_to_fieldsets', '=', '1')->get();
            if ($fields->count() > 0) {
                foreach ($fields as $field) {
                    $field_ids[] = $field->id;
                }

                $fieldset->fields()->sync($field_ids);
            }

            return redirect()->route('fieldsets.show', [$fieldset->id, 'field_category' => $fieldset->field_category])
                ->with('success', trans('admin/custom_fields/message.fieldset.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($validator);
    }

    /**
     * Presents edit form for fieldset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int  $id
     * @since [v6.0.14]
     */
    public function edit($id) : View | RedirectResponse
    {
        $this->authorize('create', CustomField::class);

        if ($fieldset = CustomFieldset::find($id)) {
            return view('custom_fields.fieldsets.edit')->with('item', $fieldset);
        }

        return redirect()->route('fields.index')->with('error', trans('admin/custom_fields/general.fieldset_does_not_exist', ['id' => $id]));

    }

    /**
     * Saves updated fieldset data
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int  $id
     * @since [v6.0.14]
     */
    public function update(Request $request, $id) : RedirectResponse
    {
        $this->authorize('create', CustomField::class);

        if ($fieldset = CustomFieldset::find($id)) {

            $fieldset->name = $request->input('name');

            if ($fieldset->save()) {
                return redirect()->route('fields.index')->with('success', trans('admin/custom_fields/general.fieldset_updated'));
            }

            return redirect()->back()->withInput()->withErrors($fieldset->getErrors());

        }

        return redirect()->route('fields.index')->with('error', trans('admin/custom_fields/general.fieldset_does_not_exist', ['id' => $id]));
    }

    /**
     * Validates a custom fieldset and then deletes if it has no models associated.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @param  int $id
     * @since [v1.8]
     */
    public function destroy($id) : RedirectResponse
    {
        $fieldset = CustomFieldset::find($id);

        $this->authorize('delete', $fieldset);

        if ($fieldset) {
            $models = AssetModel::where('fieldset_id', '=', $id);
            if ($models->count() == 0) {
                $fieldset->delete();

                return redirect()->route('fields.index')->with('success', trans('admin/custom_fields/message.fieldset.delete.success'));
            }

            return redirect()->route('fields.index')->with('error', trans('admin/custom_fields/message.fieldset.delete.in_use'));
        }

        return redirect()->route('fields.index')->with('error', trans('admin/custom_fields/message.fieldset.does_not_exist'));
    }

    /**
     * Associate the custom field with a custom fieldset.
     *
     * @author [Brady Wetherington] [<uberbrady@gmail.com>]
     * @since [v1.8]
     */
    public function associate(Request $request, $id) : RedirectResponse
    {
        $set = CustomFieldset::find($id);

        $this->authorize('update', $set);
         // Capture field_category from the request
        $fieldCategory = $request->input('field_category', 0);
        
        if ($request->filled('field_id')) {
            
            foreach ($set->fields as $field) {
                if ($field->id == $request->input('field_id')) {
                    return redirect()->route('fieldsets.show', [$id, 'field_category' => $fieldCategory])->withInput()->withErrors(['field_id' => trans('admin/custom_fields/message.field.already_added')]);
                }
            }
            
            $results = $set->fields()->attach($request->input('field_id'), ['required' => ($request->input('required') == 'on'), 'order' => (int)$request->input('order', 1),
            'field_category' => $fieldCategory,]);

            return redirect()->route('fieldsets.show', [$id, 'field_category' => $fieldCategory])->with('success', trans('admin/custom_fields/message.field.create.assoc_success'));
        }

        return redirect()->route('fieldsets.show', [$id, 'field_category' => $fieldCategory])->with('error', trans('admin/custom_fields/message.field.none_selected'));
    }

    /**
     * Set the field in a fieldset to required
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.0]
     */
    public function makeFieldRequired($fieldset_id, $field_id) : RedirectResponse
    {
        $this->authorize('update', CustomField::class);
        $field = CustomField::findOrFail($field_id);
        $fieldset = CustomFieldset::findOrFail($fieldset_id);
        $fields[$field->id] = ['required' => 1];
        $fieldset->fields()->syncWithoutDetaching($fields);

        return redirect()->route('fieldsets.show', ['fieldset' => $fieldset_id])
            ->with('success', trans('Field successfully set to required'));
    }

    /**
     * Set the field in a fieldset to optional
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.0]
     */
    public function makeFieldOptional($fieldset_id, $field_id) : RedirectResponse
    {
        $this->authorize('update', CustomField::class);
        $field = CustomField::findOrFail($field_id);
        $fieldset = CustomFieldset::findOrFail($fieldset_id);
        $fields[$field->id] = ['required' => 0];
        $fieldset->fields()->syncWithoutDetaching($fields);

        return redirect()->route('fieldsets.show', ['fieldset' => $fieldset_id])
            ->with('success', trans('Field successfully set to optional'));
    }
}
