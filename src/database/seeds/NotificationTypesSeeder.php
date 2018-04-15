<?php

/*
 * This file is part of SeAT
 *
 * Copyright (C) 2015, 2016, 2017  Leon Jacobs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace Seat\Services\database\seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('eve_notification_types')->delete();

        DB::table('eve_notification_types')->insert([
            ['id' => 1, 'desc' => 'Old Notifications'],
            ['id' => 2, 'desc' => 'Member Biomassed'],
            ['id' => 3, 'desc' => 'Medal Awarded'],
            ['id' => 4, 'desc' => 'Alliance Maintenance Bill'],
            ['id' => 5, 'desc' => 'Alliance War Declared'],
            ['id' => 6, 'desc' => 'Alliance War Surrender'],
            ['id' => 7, 'desc' => 'Alliance War Retracted'],
            ['id' => 8, 'desc' => 'Alliance War Invalidated'],
            ['id' => 9, 'desc' => 'Pilot Billed'],
            ['id' => 10, 'desc' => 'Organization Billed'],
            ['id' => 11, 'desc' => 'Insufficient Funds To Pay Bill'],
            ['id' => 12, 'desc' => 'Bill Paid By Pilot'],
            ['id' => 13, 'desc' => 'Bill Paid By Organization'],
            ['id' => 14, 'desc' => 'Capsuleer Bounty Payment'],
            ['id' => 15, 'desc' => 'Unknown'],
            ['id' => 16, 'desc' => 'New Application To Join Corporation'],
            ['id' => 17, 'desc' => 'Your Corporate Application Rejected'],
            ['id' => 18, 'desc' => 'Your Corporate Application Accepted'],
            ['id' => 19, 'desc' => 'Corporation Tax Change'],
            ['id' => 20, 'desc' => 'Corporation News'],
            ['id' => 21, 'desc' => 'Pilot Left Corporation'],
            ['id' => 22, 'desc' => 'New Corporation CEO'],
            ['id' => 23, 'desc' => 'Corporate Dividend Payout'],
            ['id' => 25, 'desc' => 'Corporate Vote Notification'],
            ['id' => 26, 'desc' => 'CEO Roles Revoked During Vote'],
            ['id' => 27, 'desc' => 'Corporation War Declared'],
            ['id' => 28, 'desc' => 'Corporation War Fighting'],
            ['id' => 29, 'desc' => 'Corporation War Surrender'],
            ['id' => 30, 'desc' => 'Corporation War Retracted'],
            ['id' => 31, 'desc' => 'Corporation War Invalidated'],
            ['id' => 32, 'desc' => 'Container Password'],
            ['id' => 33, 'desc' => 'Customs Notification'],
            ['id' => 34, 'desc' => 'Rookie Ship Replacement'],
            ['id' => 35, 'desc' => 'Insurance Payment'],
            ['id' => 36, 'desc' => 'Insurance Invalidated'],
            ['id' => 37, 'desc' => 'Alliance Sovereignty Claim Failed'],
            ['id' => 38, 'desc' => 'Corporate Sovereignty Claim Failed'],
            ['id' => 39, 'desc' => 'Alliance Sovereignty Bill Due'],
            ['id' => 40, 'desc' => 'Corporate Alliance Bill Due'],
            ['id' => 41, 'desc' => 'Alliance Sovereignty Claim Lost'],
            ['id' => 42, 'desc' => 'Corporate Sovereignty Claim Lost'],
            ['id' => 43, 'desc' => 'Alliance Sovereignty Claim Acquired'],
            ['id' => 44, 'desc' => 'Corporate Sovereignty Claim Acquired'],
            ['id' => 45, 'desc' => 'Structure Anchoring'],
            ['id' => 46, 'desc' => 'Sovereignty Structures Vulnerable'],
            ['id' => 47, 'desc' => 'Sovereignty Structures Invulnerable'],
            ['id' => 48, 'desc' => 'Sovereignty Blockade Unit Active'],
            ['id' => 49, 'desc' => 'Structure Lost'],
            ['id' => 50, 'desc' => 'Office Lease Expiration'],
            ['id' => 51, 'desc' => 'Clone Contract Revoked 1'],
            ['id' => 52, 'desc' => 'Clone Moved'],
            ['id' => 53, 'desc' => 'Clone Contract Revoked 2'],
            ['id' => 54, 'desc' => 'Insurance Expired'],
            ['id' => 55, 'desc' => 'Insurance Issued'],
            ['id' => 56, 'desc' => 'Jump Clone Deleted'],
            ['id' => 57, 'desc' => 'Jump Clone Destruction'],
            ['id' => 58, 'desc' => 'Corporation Has Joined Faction'],
            ['id' => 59, 'desc' => 'Corporation Has Left Faction'],
            ['id' => 60, 'desc' => 'Corporation Expelled From Faction'],
            ['id' => 61, 'desc' => 'Pilot Expelled From Faction'],
            ['id' => 62, 'desc' => 'Corporation Faction Standing Warning'],
            ['id' => 63, 'desc' => 'Pilot Faction Standing Warning'],
            ['id' => 64, 'desc' => 'Pilot Loses Faction Rank'],
            ['id' => 65, 'desc' => 'Pilot Gains Faction Rank'],
            ['id' => 66, 'desc' => 'Agent Moved Notice'],
            ['id' => 67, 'desc' => 'Transaction Reversal'],
            ['id' => 68, 'desc' => 'Reimbursement'],
            ['id' => 69, 'desc' => 'Pilot Located'],
            ['id' => 70, 'desc' => 'Research Mission Available'],
            ['id' => 71, 'desc' => 'Mission Offer Expiration'],
            ['id' => 72, 'desc' => 'Mission Failure'],
            ['id' => 73, 'desc' => 'Special Mission Available'],
            ['id' => 74, 'desc' => 'Tutorial Program'],
            ['id' => 75, 'desc' => 'Tower Under Attack Alert'],
            ['id' => 76, 'desc' => 'Tower Resource Alert'],
            ['id' => 77, 'desc' => 'Station Under Attack'],
            ['id' => 78, 'desc' => 'Station Changed'],
            ['id' => 79, 'desc' => 'Station Conquered'],
            ['id' => 80, 'desc' => 'Station Aggression'],
            ['id' => 81, 'desc' => 'Corporation Joining Faction'],
            ['id' => 82, 'desc' => 'Corporation Leaving Faction'],
            ['id' => 83, 'desc' => 'Corporation Join Faction Withdrawn'],
            ['id' => 84, 'desc' => 'Corporation Leave Faction Withdrawn'],
            ['id' => 85, 'desc' => 'Corporate Liquidation Settlement'],
            ['id' => 86, 'desc' => 'Sovereignty TCU Damage'],
            ['id' => 87, 'desc' => 'Sovereignty SBU Damage'],
            ['id' => 88, 'desc' => 'Sovereignty IHUB Damage'],
            ['id' => 89, 'desc' => 'Added As Contact'],
            ['id' => 90, 'desc' => 'Contact Level Modified'],
            ['id' => 91, 'desc' => 'Incursion Completed'],
            ['id' => 92, 'desc' => 'Kicked From Corporation'],
            ['id' => 93, 'desc' => 'Orbital Structure Attacked'],
            ['id' => 94, 'desc' => 'Orbital Structure Reinforced'],
            ['id' => 95, 'desc' => 'Structure Ownership Transferred'],
            ['id' => 96, 'desc' => 'Alliance Faction Standing Warning'],
            ['id' => 97, 'desc' => 'Alliance Expelled From Faction'],
            ['id' => 98, 'desc' => 'Corporation Joined Alliance At War'],
            ['id' => 99, 'desc' => 'Defender Ally Joins War'],
            ['id' => 100, 'desc' => 'Aggressor Ally Joins War'],
            ['id' => 101, 'desc' => 'Corporation Joins War As Ally'],
            ['id' => 102, 'desc' => 'War Ally Offer Received'],
            ['id' => 103, 'desc' => 'Surrender Offer Received'],
            ['id' => 104, 'desc' => 'Surrender Declined'],
            ['id' => 105, 'desc' => 'Faction Kill Event LP'],
            ['id' => 106, 'desc' => 'Faction Strategic Event LP'],
            ['id' => 107, 'desc' => 'Strategic Event LP Disqualification'],
            ['id' => 108, 'desc' => 'Kill Event LP Disqualification'],
            ['id' => 109, 'desc' => 'War Ally Agreement Cancelled'],
            ['id' => 110, 'desc' => 'War Ally Offer Declined'],
            ['id' => 111, 'desc' => 'Bounty On You Claimed'],
            ['id' => 112, 'desc' => 'Bounty Placed On You'],
            ['id' => 113, 'desc' => 'Bounty Placed On Corporation'],
            ['id' => 114, 'desc' => 'Bounty Placed On Alliance'],
            ['id' => 115, 'desc' => 'Kill Right Available'],
            ['id' => 116, 'desc' => 'Kill Right Available To All'],
            ['id' => 117, 'desc' => 'Kill Right Earned'],
            ['id' => 118, 'desc' => 'Kill Right Used'],
            ['id' => 119, 'desc' => 'Kill Right Unavailable'],
            ['id' => 120, 'desc' => 'Kill Right Unavailable To All'],
            ['id' => 121, 'desc' => 'War Declaration'],
            ['id' => 122, 'desc' => 'Surrender Offered'],
            ['id' => 123, 'desc' => 'Surrender Accepted'],
            ['id' => 124, 'desc' => 'War Made Mutual'],
            ['id' => 125, 'desc' => 'War Retracted'],
            ['id' => 126, 'desc' => 'You Offered War Ally'],
            ['id' => 127, 'desc' => 'You Accepted War Ally'],
            ['id' => 128, 'desc' => 'Mercenary Invitation Accepted'],
            ['id' => 129, 'desc' => 'Mercenary Invitation Rejected'],
            ['id' => 130, 'desc' => 'Mercenary Application Withdrawn'],
            ['id' => 131, 'desc' => 'Mercenary Application Accepted'],
            ['id' => 132, 'desc' => 'Corporation District Attacked'],
            ['id' => 133, 'desc' => 'Friendly Fire Standings Loss'],
            ['id' => 134, 'desc' => 'ESS Pool Taken'],
            ['id' => 135, 'desc' => 'ESS Pool Shared'],
            ['id' => 136, 'desc' => 'Unknown'],
            ['id' => 137, 'desc' => 'Unknown'],
            ['id' => 138, 'desc' => 'Clone Activation'],
            ['id' => 139, 'desc' => 'You have been invited to join a Corporation'],
            ['id' => 140, 'desc' => 'Kill report - Victim'],
            ['id' => 141, 'desc' => 'Kill report - Final blow'],
            ['id' => 142, 'desc' => 'Your Corporate Application Rejected'],
            ['id' => 143, 'desc' => 'Corp Friendly - fire Enable - timer started'],
            ['id' => 144, 'desc' => 'Corp Friendly - fire Disable - timer started'],
            ['id' => 145, 'desc' => 'Corp Friendly - fire Enable - timer completed'],
            ['id' => 146, 'desc' => 'Corp Friendly - fire Disable - timer completed'],
            ['id' => 147, 'desc' => 'Sovereignty Structure Capture Started'],
            ['id' => 148, 'desc' => 'Sovereignty Service Enabled'],
            ['id' => 149, 'desc' => 'Sovereignty Service Disabled'],
            ['id' => 150, 'desc' => 'Sovereignty Service Half Captured'],
            ['id' => 151, 'desc' => 'Unknown'],
            ['id' => 152, 'desc' => 'IHub Bill Expiring'],
            ['id' => 160, 'desc' => 'Sovereignty Structures Reinforced'],
            ['id' => 161, 'desc' => 'Command Nodes Decloaking'],
            ['id' => 162, 'desc' => 'Sovereignty Structure Destroyed'],
            ['id' => 163, 'desc' => 'Station Entered Freeport'],
            ['id' => 164, 'desc' => 'IHub Destroyed - Bill'],
            ['id' => 165, 'desc' => 'Alliance Capital Changed'],
            ['id' => 1002, 'desc' => 'Skill Queue Empty'],
            ['id' => 1000, 'desc' => 'Skill Training Complete'],
            ['id' => 1003, 'desc' => 'Unread Email Summary'],
            ['id' => 1004, 'desc' => 'New Mail Recieved'],
            ['id' => 1005, 'desc' => 'Unused Skill Points'],
            ['id' => 1006, 'desc' => 'Contracts assigned to you'],
            ['id' => 1007, 'desc' => 'Contracts need your attention'],
            ['id' => 1010, 'desc' => 'Task Completed'],
            ['id' => 1011, 'desc' => 'Opportunity Completed'],
            ['id' => 1020, 'desc' => 'New Redeemable Item'],
            ['id' => 1030, 'desc' => 'Plex Donation'],
            ['id' => 1031, 'desc' => 'Plex Donation'],
            ['id' => 2001, 'desc' => 'Watched Contact Online'],
            ['id' => 2002, 'desc' => 'Watched Contact Offline'],]);

    }
}
