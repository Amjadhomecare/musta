<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AllAccountLedgersSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now('Asia/Dubai');

        $rows = [
            // class      sub_class                      group                 ledger               note      amount
            ['Assets',    'Current assets',              'maid',               'maids',             'special', 0],
            ['Liability', 'Other current libilities',    'Account Payable',    'P4_MAIDS_PAYROLL',  'special', 0],
            ['Revenue',   'Other current libilities',    'Package4 Income',    'P4_REVENUE',        'special', 0],
            ['Liability', 'Other current libilities',    'Current liability',  'VAT',               'special', 0],
            ['Revenue',   'Other current libilities',    'Package 1 Income',   'P1_REVENUE',        'special', 0],
            ['Liability', 'Long Term Liabilites',        'Package 1 Income',   'PARTIAL_DEDCUTION', 'special', 0],
            ['Liability', 'Other current libilities',    'Account Payable',    'P1_MAID_SALARY',    'special', 0],
            ['Assets',    'Other current libilities',    'Current assets',     'PRIVATE',           'special', 0],
        ];

        foreach ($rows as [$class, $subClass, $group, $ledger, $note, $amount]) {
            $exists = DB::table('all_account_ledger__d_b_s')
                ->where('ledger', $ledger)
                ->exists();

            if (!$exists) {
                DB::table('all_account_ledger__d_b_s')->insert([
                    'class'      => $class,
                    'sub_class'  => $subClass,
                    'group'      => $group,
                    'ledger'     => $ledger,
                    'note'       => $note,
                    'amount'     => $amount,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
