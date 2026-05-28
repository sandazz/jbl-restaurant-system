<?php

namespace App\Http\Controllers;

use App\Models\TierDiscount;
use Illuminate\Http\Request;

class TierDiscountController extends Controller
{
    /** GET /tier-discounts */
    public function index()
    {
        $tiers   = TierDiscount::orderBy('id')->get();
        $modules = auth()->user()->role->modules()->get();

        return view('modules.tier-discounts', compact('tiers', 'modules'));
    }

    /** POST /tier-discounts — add a new tier */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tier'                 => 'required|string|max:50|unique:tier_discounts,tier',
            'discount_percentage'  => 'required|numeric|min:0|max:100',
            'color'                => 'required|in:yellow,blue,purple,gray,green,red,orange,indigo,pink,teal',
            'is_active'            => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        TierDiscount::create($validated);

        return back()->with('success', "Tier \"{$validated['tier']}\" added successfully.");
    }

    /** POST /tier-discounts/save-all — update existing tiers in bulk */
    public function saveAll(Request $request)
    {
        $request->validate([
            'tiers'                          => 'required|array',
            'tiers.*.id'                     => 'required|exists:tier_discounts,id',
            'tiers.*.discount_percentage'    => 'required|numeric|min:0|max:100',
            'tiers.*.color'                  => 'required|in:yellow,blue,purple,gray,green,red,orange,indigo,pink,teal',
            'tiers.*.is_active'              => 'nullable|boolean',
        ]);

        foreach ($request->tiers as $row) {
            TierDiscount::where('id', $row['id'])->update([
                'discount_percentage' => $row['discount_percentage'],
                'color'               => $row['color'],
                'is_active'           => isset($row['is_active']),
            ]);
        }

        return back()->with('success', 'Tier discounts updated successfully.');
    }

    /** DELETE /tier-discounts/{tierDiscount} */
    public function destroy(TierDiscount $tierDiscount)
    {
        if ($tierDiscount->customerCount() > 0) {
            return back()->withErrors(['delete' => "Cannot delete \"{$tierDiscount->tier}\" — {$tierDiscount->customerCount()} customer(s) are assigned to it."]);
        }

        $tierDiscount->delete();

        return back()->with('success', "Tier \"{$tierDiscount->tier}\" deleted.");
    }
}
