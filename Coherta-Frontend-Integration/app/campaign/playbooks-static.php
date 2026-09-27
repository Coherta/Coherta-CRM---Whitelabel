<?php
function coherta_campaign_playbook_workflow_founder_sales(): array
{
    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_connect', 'type' => 'connect', 'title' => 'Founder Connect', 'x' => 520, 'y' => 330, 'config' => ['message' => 'Hi {lead.first_name}. I can see that you work with growth at {lead.company_name}. Would it make sense if I sent a short explanation of Coherta?']],
            ['id' => 'pb_if_accepted', 'type' => 'if_accepted', 'title' => 'If Accepted', 'x' => 360, 'y' => 540, 'config' => new stdClass()],
            ['id' => 'pb_wait', 'type' => 'wait', 'title' => 'Wait 1 day', 'x' => 360, 'y' => 750, 'config' => ['duration' => 1, 'unit' => 'days']],
            ['id' => 'pb_write', 'type' => 'write', 'title' => 'Founder DM', 'x' => 360, 'y' => 960, 'config' => ['template' => 'Hi {lead.first_name}. Thanks for connecting. We help B2B teams find the right decision-makers and follow up when the relationship is warm. Should I send a concrete example?', 'system_prompt' => 'Keep the message short, founder-led, relevant and easy to reply to.']],
            ['id' => 'pb_monitor', 'type' => 'add_to_stakeholder_monitoring', 'title' => 'Add to Lead Monitoring', 'x' => 360, 'y' => 1170, 'config' => new stdClass()],
            ['id' => 'pb_not_accepted', 'type' => 'not_accepted', 'title' => 'Not Accepted', 'x' => 690, 'y' => 540, 'config' => new stdClass()],
            ['id' => 'pb_cleanup_wait', 'type' => 'wait', 'title' => 'Wait 10 days', 'x' => 690, 'y' => 750, 'config' => ['duration' => 10, 'unit' => 'days']],
            ['id' => 'pb_delete_request', 'type' => 'delete_connection_request', 'title' => 'Delete Connection Request', 'x' => 690, 'y' => 960, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_connect', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_connect', 'to' => 'pb_if_accepted', 'label' => 'Accepted'],
            ['id' => 'pb_edge_3', 'from' => 'pb_if_accepted', 'to' => 'pb_wait', 'label' => 'Yes'],
            ['id' => 'pb_edge_4', 'from' => 'pb_wait', 'to' => 'pb_write', 'label' => ''],
            ['id' => 'pb_edge_5', 'from' => 'pb_write', 'to' => 'pb_monitor', 'label' => ''],
            ['id' => 'pb_edge_6', 'from' => 'pb_connect', 'to' => 'pb_not_accepted', 'label' => 'Not accepted'],
            ['id' => 'pb_edge_7', 'from' => 'pb_not_accepted', 'to' => 'pb_cleanup_wait', 'label' => ''],
            ['id' => 'pb_edge_8', 'from' => 'pb_cleanup_wait', 'to' => 'pb_delete_request', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_arr(): array
{
    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_visit', 'type' => 'visit', 'title' => 'Visit Person', 'x' => 520, 'y' => 330, 'config' => new stdClass()],
            ['id' => 'pb_wait_1', 'type' => 'wait', 'title' => 'Wait 2 days', 'x' => 520, 'y' => 540, 'config' => ['duration' => 2, 'unit' => 'days']],
            ['id' => 'pb_connect', 'type' => 'connect', 'title' => 'Auto Connect', 'x' => 520, 'y' => 750, 'config' => ['message' => 'Hi {lead.first_name}. I work with B2B outbound and thought it could be relevant to connect.']],
            ['id' => 'pb_if_accepted', 'type' => 'if_accepted', 'title' => 'If Accepted', 'x' => 390, 'y' => 960, 'config' => new stdClass()],
            ['id' => 'pb_wait_2', 'type' => 'wait', 'title' => 'Wait 1 day', 'x' => 390, 'y' => 1170, 'config' => ['duration' => 1, 'unit' => 'days']],
            ['id' => 'pb_write', 'type' => 'write', 'title' => 'Short DM', 'x' => 390, 'y' => 1380, 'config' => ['template' => 'Hi {lead.first_name}. Thanks for connecting. We help teams build more structured lead lists and follow up on LinkedIn without losing context. Should I send a short example?', 'system_prompt' => 'Make the message concise, concrete and low pressure.']],
            ['id' => 'pb_like', 'type' => 'like', 'title' => 'Like Post', 'x' => 390, 'y' => 1590, 'config' => new stdClass()],
            ['id' => 'pb_not_accepted', 'type' => 'not_accepted', 'title' => 'Not Accepted', 'x' => 690, 'y' => 960, 'config' => new stdClass()],
            ['id' => 'pb_cleanup_wait', 'type' => 'wait', 'title' => 'Wait 14 days', 'x' => 690, 'y' => 1170, 'config' => ['duration' => 14, 'unit' => 'days']],
            ['id' => 'pb_delete_request', 'type' => 'delete_connection_request', 'title' => 'Delete Connection Request', 'x' => 690, 'y' => 1380, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_visit', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_visit', 'to' => 'pb_wait_1', 'label' => ''],
            ['id' => 'pb_edge_3', 'from' => 'pb_wait_1', 'to' => 'pb_connect', 'label' => ''],
            ['id' => 'pb_edge_4', 'from' => 'pb_connect', 'to' => 'pb_if_accepted', 'label' => 'Accepted'],
            ['id' => 'pb_edge_5', 'from' => 'pb_if_accepted', 'to' => 'pb_wait_2', 'label' => 'Yes'],
            ['id' => 'pb_edge_6', 'from' => 'pb_wait_2', 'to' => 'pb_write', 'label' => ''],
            ['id' => 'pb_edge_7', 'from' => 'pb_write', 'to' => 'pb_like', 'label' => ''],
            ['id' => 'pb_edge_8', 'from' => 'pb_connect', 'to' => 'pb_not_accepted', 'label' => 'Not accepted'],
            ['id' => 'pb_edge_9', 'from' => 'pb_not_accepted', 'to' => 'pb_cleanup_wait', 'label' => ''],
            ['id' => 'pb_edge_10', 'from' => 'pb_cleanup_wait', 'to' => 'pb_delete_request', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_find_connect_call(): array
{
    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_visit', 'type' => 'visit', 'title' => 'Visit Person', 'x' => 520, 'y' => 330, 'config' => new stdClass()],
            ['id' => 'pb_wait_before_connect', 'type' => 'wait', 'title' => 'Wait 2 days', 'x' => 520, 'y' => 540, 'config' => ['duration' => 2, 'unit' => 'days', 'durationValue' => 2, 'durationUnit' => 'days']],
            ['id' => 'pb_connect', 'type' => 'connect', 'title' => 'Connect', 'x' => 520, 'y' => 750, 'config' => ['message' => 'Hi {lead.first_name}. I can see that you work at {lead.company_name}. I work with B2B leads and follow-up, so I thought it made sense to connect.', 'connectMessage' => 'Hi {lead.first_name}. I can see that you work at {lead.company_name}. I work with B2B leads and follow-up, so I thought it made sense to connect.']],
            ['id' => 'pb_if_accepted', 'type' => 'if_accepted', 'title' => 'If Accepted', 'x' => 370, 'y' => 960, 'config' => new stdClass()],
            ['id' => 'pb_send_to_lead_list', 'type' => 'send_to_lead_list', 'title' => 'Send to Leadlist', 'x' => 370, 'y' => 1170, 'config' => ['mode' => 'new', 'newLeadListName' => 'Accepted connections - call', 'leadListTitle' => 'Accepted connections - call', 'source' => 'people']],
            ['id' => 'pb_monitor', 'type' => 'add_to_stakeholder_monitoring', 'title' => 'Lead Monitoring', 'x' => 370, 'y' => 1380, 'config' => new stdClass()],
            ['id' => 'pb_enrich', 'type' => 'enrich_stakeholder_monitoring', 'title' => 'Enrich lead in Lead Monitoring', 'x' => 370, 'y' => 1590, 'config' => new stdClass()],
            ['id' => 'pb_not_accepted', 'type' => 'not_accepted', 'title' => 'Not Accepted', 'x' => 700, 'y' => 960, 'config' => new stdClass()],
            ['id' => 'pb_cleanup_wait', 'type' => 'wait', 'title' => 'Wait 35 days', 'x' => 700, 'y' => 1170, 'config' => ['duration' => 35, 'unit' => 'days', 'durationValue' => 35, 'durationUnit' => 'days']],
            ['id' => 'pb_delete_request', 'type' => 'delete_connection_request', 'title' => 'Delete Connection Request', 'x' => 700, 'y' => 1380, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_visit', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_visit', 'to' => 'pb_wait_before_connect', 'label' => ''],
            ['id' => 'pb_edge_3', 'from' => 'pb_wait_before_connect', 'to' => 'pb_connect', 'label' => ''],
            ['id' => 'pb_edge_4', 'from' => 'pb_connect', 'to' => 'pb_if_accepted', 'label' => 'Accepted'],
            ['id' => 'pb_edge_5', 'from' => 'pb_if_accepted', 'to' => 'pb_send_to_lead_list', 'label' => 'Yes'],
            ['id' => 'pb_edge_6', 'from' => 'pb_send_to_lead_list', 'to' => 'pb_monitor', 'label' => ''],
            ['id' => 'pb_edge_7', 'from' => 'pb_monitor', 'to' => 'pb_enrich', 'label' => ''],
            ['id' => 'pb_edge_8', 'from' => 'pb_connect', 'to' => 'pb_not_accepted', 'label' => 'Not accepted'],
            ['id' => 'pb_edge_9', 'from' => 'pb_not_accepted', 'to' => 'pb_cleanup_wait', 'label' => ''],
            ['id' => 'pb_edge_10', 'from' => 'pb_cleanup_wait', 'to' => 'pb_delete_request', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_bluenordix_stakeholder_transfer(): array
{
    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_monitor', 'type' => 'add_to_stakeholder_monitoring', 'title' => 'Add to Lead Monitoring', 'x' => 520, 'y' => 330, 'config' => new stdClass()],
            ['id' => 'pb_enrich', 'type' => 'enrich_stakeholder_monitoring', 'title' => 'Enrich lead in Lead Monitoring', 'x' => 520, 'y' => 540, 'config' => new stdClass()],
            ['id' => 'pb_bluenordix', 'type' => 'phoner_bluenordix', 'title' => 'Send to BlueNordix', 'x' => 520, 'y' => 750, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_monitor', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_monitor', 'to' => 'pb_enrich', 'label' => ''],
            ['id' => 'pb_edge_3', 'from' => 'pb_enrich', 'to' => 'pb_bluenordix', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_post_likes_whatsapp(): array
{
    $message = '';
    $whatsAppSystemPrompt = '';
    $connectMessage = 'Hi {lead.first_name}. I saw your engagement on LinkedIn and thought it made sense to connect here first.';
    $linkedinMessage = 'Hi {lead.first_name}. Thanks for connecting. I noticed your LinkedIn engagement and thought this might be relevant for your work at {lead.company_name}. Should I send a short example?';

    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_visit', 'type' => 'visit', 'title' => 'Visit Person', 'x' => 520, 'y' => 330, 'config' => new stdClass()],
            ['id' => 'pb_icp', 'type' => 'icp_screening', 'title' => 'Automated Screening', 'x' => 520, 'y' => 540, 'config' => ['icpDefinition' => 'Relevant B2B decision-makers or specialists who fit our ideal customer profile and have a clear business reason to talk.', 'matchThreshold' => 80, 'scoreThreshold' => 80]],
            ['id' => 'pb_icp_match', 'type' => 'wait', 'title' => 'Match', 'x' => 350, 'y' => 780, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'match', 'locked' => true]],
            ['id' => 'pb_no_match', 'type' => 'wait', 'title' => 'No Match', 'x' => 690, 'y' => 780, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'no_match', 'locked' => true]],
            ['id' => 'pb_wait_before_connect', 'type' => 'wait', 'title' => 'Wait 2 days', 'x' => 350, 'y' => 990, 'config' => ['duration' => 2, 'unit' => 'days', 'durationValue' => 2, 'durationUnit' => 'days']],
            ['id' => 'pb_connect', 'type' => 'connect', 'title' => 'Connect', 'x' => 350, 'y' => 1200, 'config' => ['message' => $connectMessage, 'connectMessage' => $connectMessage]],
            ['id' => 'pb_if_accepted', 'type' => 'if_accepted', 'title' => 'If Accepted', 'x' => 190, 'y' => 1410, 'config' => new stdClass()],
            ['id' => 'pb_wait_after_accept', 'type' => 'wait', 'title' => 'Wait 9 hours', 'x' => 190, 'y' => 1620, 'config' => ['duration' => 9, 'unit' => 'hours', 'durationValue' => 9, 'durationUnit' => 'hours']],
            ['id' => 'pb_write', 'type' => 'write', 'title' => 'LinkedIn DM', 'x' => 190, 'y' => 1830, 'config' => ['template' => $linkedinMessage, 'systemPrompt' => 'Keep the message warm, concise and tied to the engagement signal. Do not imply the recipient asked for a sales pitch.', 'system_prompt' => 'Keep the message warm, concise and tied to the engagement signal. Do not imply the recipient asked for a sales pitch.']],
            ['id' => 'pb_monitor', 'type' => 'add_to_stakeholder_monitoring', 'title' => 'Add to Lead Monitoring', 'x' => 190, 'y' => 2040, 'config' => new stdClass()],
            ['id' => 'pb_enrich', 'type' => 'enrich_stakeholder_monitoring', 'title' => 'Enrich lead in Lead Monitoring', 'x' => 190, 'y' => 2250, 'config' => new stdClass()],
            [
                'id' => 'pb_whatsapp',
                'type' => 'whatsapp_message',
                'title' => 'WhatsApp Message',
                'x' => 190,
                'y' => 2460,
                'config' => [
                    'template' => $message,
                    'messageTemplate' => $message,
                    'message' => $message,
                    'Message' => $message,
                    'systemPrompt' => $whatsAppSystemPrompt,
                    'whatsappSystemPrompt' => $whatsAppSystemPrompt,
                    'whatsapp_system_prompt' => $whatsAppSystemPrompt,
                    'WhatsAppSystemPrompt' => $whatsAppSystemPrompt,
                    'manualApproval' => true,
                ],
            ],
            ['id' => 'pb_not_accepted', 'type' => 'not_accepted', 'title' => 'Not Accepted', 'x' => 510, 'y' => 1410, 'config' => new stdClass()],
            ['id' => 'pb_cleanup_wait', 'type' => 'wait', 'title' => 'Wait 35 days', 'x' => 510, 'y' => 1620, 'config' => ['duration' => 35, 'unit' => 'days', 'durationValue' => 35, 'durationUnit' => 'days']],
            ['id' => 'pb_delete_request', 'type' => 'delete_connection_request', 'title' => 'Delete Connection Request', 'x' => 510, 'y' => 1830, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_visit', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_visit', 'to' => 'pb_icp', 'label' => ''],
            ['id' => 'pb_edge_3', 'from' => 'pb_icp', 'to' => 'pb_icp_match', 'label' => 'Match'],
            ['id' => 'pb_edge_4', 'from' => 'pb_icp', 'to' => 'pb_no_match', 'label' => 'No Match'],
            ['id' => 'pb_edge_5', 'from' => 'pb_icp_match', 'to' => 'pb_wait_before_connect', 'label' => ''],
            ['id' => 'pb_edge_6', 'from' => 'pb_wait_before_connect', 'to' => 'pb_connect', 'label' => ''],
            ['id' => 'pb_edge_7', 'from' => 'pb_connect', 'to' => 'pb_if_accepted', 'label' => 'Accepted'],
            ['id' => 'pb_edge_8', 'from' => 'pb_if_accepted', 'to' => 'pb_wait_after_accept', 'label' => 'Yes'],
            ['id' => 'pb_edge_9', 'from' => 'pb_wait_after_accept', 'to' => 'pb_write', 'label' => ''],
            ['id' => 'pb_edge_10', 'from' => 'pb_write', 'to' => 'pb_monitor', 'label' => ''],
            ['id' => 'pb_edge_11', 'from' => 'pb_monitor', 'to' => 'pb_enrich', 'label' => ''],
            ['id' => 'pb_edge_12', 'from' => 'pb_enrich', 'to' => 'pb_whatsapp', 'label' => ''],
            ['id' => 'pb_edge_13', 'from' => 'pb_connect', 'to' => 'pb_not_accepted', 'label' => 'Not accepted'],
            ['id' => 'pb_edge_14', 'from' => 'pb_not_accepted', 'to' => 'pb_cleanup_wait', 'label' => ''],
            ['id' => 'pb_edge_15', 'from' => 'pb_cleanup_wait', 'to' => 'pb_delete_request', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_post_likes_linkedin_follow_up(): array
{
    $connectMessage = 'Hi {lead.first_name}. I saw your engagement on LinkedIn and thought it made sense to connect.';
    $dmMessage = 'Hi {lead.first_name}. Thanks for connecting. I noticed your engagement and thought this might be relevant for your work at {lead.company_name}. Is it worth sharing a short example?';

    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_visit', 'type' => 'visit', 'title' => 'Visit Person', 'x' => 520, 'y' => 330, 'config' => new stdClass()],
            ['id' => 'pb_icp', 'type' => 'icp_screening', 'title' => 'Automated Screening', 'x' => 520, 'y' => 540, 'config' => ['icpDefinition' => 'Relevant B2B decision-makers or specialists who match the campaign ICP.', 'matchThreshold' => 80, 'scoreThreshold' => 80]],
            ['id' => 'pb_icp_match', 'type' => 'wait', 'title' => 'Match', 'x' => 350, 'y' => 780, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'match', 'locked' => true]],
            ['id' => 'pb_no_match', 'type' => 'wait', 'title' => 'No Match', 'x' => 690, 'y' => 780, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'no_match', 'locked' => true]],
            ['id' => 'pb_wait_before_connect', 'type' => 'wait', 'title' => 'Wait 2 days', 'x' => 350, 'y' => 990, 'config' => ['duration' => 2, 'unit' => 'days', 'durationValue' => 2, 'durationUnit' => 'days']],
            ['id' => 'pb_connect', 'type' => 'connect', 'title' => 'Connect', 'x' => 350, 'y' => 1200, 'config' => ['message' => $connectMessage, 'connectMessage' => $connectMessage]],
            ['id' => 'pb_if_accepted', 'type' => 'if_accepted', 'title' => 'If Accepted', 'x' => 190, 'y' => 1410, 'config' => new stdClass()],
            ['id' => 'pb_wait_after_accept', 'type' => 'wait', 'title' => 'Wait 9 hours', 'x' => 190, 'y' => 1620, 'config' => ['duration' => 9, 'unit' => 'hours', 'durationValue' => 9, 'durationUnit' => 'hours']],
            ['id' => 'pb_write', 'type' => 'write', 'title' => 'LinkedIn DM', 'x' => 190, 'y' => 1830, 'config' => ['template' => $dmMessage, 'systemPrompt' => 'Keep the message warm, specific to the engagement signal and easy to reply to.', 'system_prompt' => 'Keep the message warm, specific to the engagement signal and easy to reply to.']],
            ['id' => 'pb_monitor', 'type' => 'add_to_stakeholder_monitoring', 'title' => 'Add to Lead Monitoring', 'x' => 190, 'y' => 2040, 'config' => new stdClass()],
            ['id' => 'pb_not_accepted', 'type' => 'not_accepted', 'title' => 'Not Accepted', 'x' => 510, 'y' => 1410, 'config' => new stdClass()],
            ['id' => 'pb_cleanup_wait', 'type' => 'wait', 'title' => 'Wait 35 days', 'x' => 510, 'y' => 1620, 'config' => ['duration' => 35, 'unit' => 'days', 'durationValue' => 35, 'durationUnit' => 'days']],
            ['id' => 'pb_delete_request', 'type' => 'delete_connection_request', 'title' => 'Delete Connection Request', 'x' => 510, 'y' => 1830, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_visit', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_visit', 'to' => 'pb_icp', 'label' => ''],
            ['id' => 'pb_edge_3', 'from' => 'pb_icp', 'to' => 'pb_icp_match', 'label' => 'Match'],
            ['id' => 'pb_edge_4', 'from' => 'pb_icp', 'to' => 'pb_no_match', 'label' => 'No Match'],
            ['id' => 'pb_edge_5', 'from' => 'pb_icp_match', 'to' => 'pb_wait_before_connect', 'label' => ''],
            ['id' => 'pb_edge_6', 'from' => 'pb_wait_before_connect', 'to' => 'pb_connect', 'label' => ''],
            ['id' => 'pb_edge_7', 'from' => 'pb_connect', 'to' => 'pb_if_accepted', 'label' => 'Accepted'],
            ['id' => 'pb_edge_8', 'from' => 'pb_if_accepted', 'to' => 'pb_wait_after_accept', 'label' => 'Yes'],
            ['id' => 'pb_edge_9', 'from' => 'pb_wait_after_accept', 'to' => 'pb_write', 'label' => ''],
            ['id' => 'pb_edge_10', 'from' => 'pb_write', 'to' => 'pb_monitor', 'label' => ''],
            ['id' => 'pb_edge_11', 'from' => 'pb_connect', 'to' => 'pb_not_accepted', 'label' => 'Not accepted'],
            ['id' => 'pb_edge_12', 'from' => 'pb_not_accepted', 'to' => 'pb_cleanup_wait', 'label' => ''],
            ['id' => 'pb_edge_13', 'from' => 'pb_cleanup_wait', 'to' => 'pb_delete_request', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_ai_icp_screening(): array
{
    $connectMessage = 'Hi {lead.first_name}. I can see that your role at {lead.company_name} is close to the problem we work with. Would it make sense to connect?';
    $dmMessage = 'Hi {lead.first_name}. Thanks for connecting. The reason I reached out is that your profile matched a specific ICP we are working with. Should I send the short context?';

    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_icp', 'type' => 'icp_screening', 'title' => 'Automated Screening', 'x' => 520, 'y' => 330, 'config' => ['icpDefinition' => 'The person should match our ideal customer profile by role, company context and visible business relevance.', 'matchThreshold' => 80, 'scoreThreshold' => 80]],
            ['id' => 'pb_icp_match', 'type' => 'wait', 'title' => 'Match', 'x' => 350, 'y' => 570, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'match', 'locked' => true]],
            ['id' => 'pb_no_match', 'type' => 'wait', 'title' => 'No Match', 'x' => 690, 'y' => 570, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'no_match', 'locked' => true]],
            ['id' => 'pb_visit', 'type' => 'visit', 'title' => 'Visit Person', 'x' => 350, 'y' => 780, 'config' => new stdClass()],
            ['id' => 'pb_wait_before_connect', 'type' => 'wait', 'title' => 'Wait 3 days', 'x' => 350, 'y' => 990, 'config' => ['duration' => 3, 'unit' => 'days', 'durationValue' => 3, 'durationUnit' => 'days']],
            ['id' => 'pb_connect', 'type' => 'connect', 'title' => 'Connect', 'x' => 350, 'y' => 1200, 'config' => ['message' => $connectMessage, 'connectMessage' => $connectMessage]],
            ['id' => 'pb_if_accepted', 'type' => 'if_accepted', 'title' => 'If Accepted', 'x' => 190, 'y' => 1410, 'config' => new stdClass()],
            ['id' => 'pb_wait_after_accept', 'type' => 'wait', 'title' => 'Wait 1 day', 'x' => 190, 'y' => 1620, 'config' => ['duration' => 1, 'unit' => 'days', 'durationValue' => 1, 'durationUnit' => 'days']],
            ['id' => 'pb_write', 'type' => 'write', 'title' => 'LinkedIn DM', 'x' => 190, 'y' => 1830, 'config' => ['template' => $dmMessage, 'systemPrompt' => 'Keep the message concise and explain the relevance without mentioning a score.', 'system_prompt' => 'Keep the message concise and explain the relevance without mentioning a score.']],
            ['id' => 'pb_monitor', 'type' => 'add_to_stakeholder_monitoring', 'title' => 'Add to Lead Monitoring', 'x' => 190, 'y' => 2040, 'config' => new stdClass()],
            ['id' => 'pb_enrich', 'type' => 'enrich_stakeholder_monitoring', 'title' => 'Enrich lead in Lead Monitoring', 'x' => 190, 'y' => 2250, 'config' => new stdClass()],
            ['id' => 'pb_not_accepted', 'type' => 'not_accepted', 'title' => 'Not Accepted', 'x' => 510, 'y' => 1410, 'config' => new stdClass()],
            ['id' => 'pb_cleanup_wait', 'type' => 'wait', 'title' => 'Wait 35 days', 'x' => 510, 'y' => 1620, 'config' => ['duration' => 35, 'unit' => 'days', 'durationValue' => 35, 'durationUnit' => 'days']],
            ['id' => 'pb_delete_request', 'type' => 'delete_connection_request', 'title' => 'Delete Connection Request', 'x' => 510, 'y' => 1830, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_icp', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_icp', 'to' => 'pb_icp_match', 'label' => 'Match'],
            ['id' => 'pb_edge_3', 'from' => 'pb_icp', 'to' => 'pb_no_match', 'label' => 'No Match'],
            ['id' => 'pb_edge_4', 'from' => 'pb_icp_match', 'to' => 'pb_visit', 'label' => ''],
            ['id' => 'pb_edge_5', 'from' => 'pb_visit', 'to' => 'pb_wait_before_connect', 'label' => ''],
            ['id' => 'pb_edge_6', 'from' => 'pb_wait_before_connect', 'to' => 'pb_connect', 'label' => ''],
            ['id' => 'pb_edge_7', 'from' => 'pb_connect', 'to' => 'pb_if_accepted', 'label' => 'Accepted'],
            ['id' => 'pb_edge_8', 'from' => 'pb_if_accepted', 'to' => 'pb_wait_after_accept', 'label' => 'Yes'],
            ['id' => 'pb_edge_9', 'from' => 'pb_wait_after_accept', 'to' => 'pb_write', 'label' => ''],
            ['id' => 'pb_edge_10', 'from' => 'pb_write', 'to' => 'pb_monitor', 'label' => ''],
            ['id' => 'pb_edge_11', 'from' => 'pb_monitor', 'to' => 'pb_enrich', 'label' => ''],
            ['id' => 'pb_edge_12', 'from' => 'pb_connect', 'to' => 'pb_not_accepted', 'label' => 'Not accepted'],
            ['id' => 'pb_edge_13', 'from' => 'pb_not_accepted', 'to' => 'pb_cleanup_wait', 'label' => ''],
            ['id' => 'pb_edge_14', 'from' => 'pb_cleanup_wait', 'to' => 'pb_delete_request', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_linkedin_search_calendly(): array
{
    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_visit', 'type' => 'visit', 'title' => 'Visit Person', 'x' => 520, 'y' => 330, 'config' => new stdClass()],
            ['id' => 'pb_icp', 'type' => 'icp_screening', 'title' => 'Automated Screening', 'x' => 520, 'y' => 540, 'config' => ['icpDefinition' => 'The person should be a realistic meeting candidate for the campaign offer.', 'matchThreshold' => 70, 'scoreThreshold' => 70]],
            ['id' => 'pb_icp_match', 'type' => 'wait', 'title' => 'Match', 'x' => 350, 'y' => 780, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'match', 'locked' => true]],
            ['id' => 'pb_no_match', 'type' => 'wait', 'title' => 'No Match', 'x' => 690, 'y' => 780, 'config' => ['durationValue' => 1, 'durationUnit' => 'seconds', 'icpScreeningId' => 'pb_icp', 'icpBranch' => 'no_match', 'locked' => true]],
            ['id' => 'pb_calendly', 'type' => 'calendly_booking', 'title' => 'Automatically book Calendly link', 'x' => 350, 'y' => 990, 'config' => ['startTime' => '09:00', 'endTime' => '17:00', 'calendlyUrl' => '', 'calendlyLink' => '', 'url' => '', 'minDaysAhead' => 1, 'maxDaysAhead' => 14, 'bookingWindowDays' => 14, 'timezone' => 'Europe/Copenhagen', 'bookingNote' => '']],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_visit', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_visit', 'to' => 'pb_icp', 'label' => ''],
            ['id' => 'pb_edge_3', 'from' => 'pb_icp', 'to' => 'pb_icp_match', 'label' => 'Match'],
            ['id' => 'pb_edge_4', 'from' => 'pb_icp', 'to' => 'pb_no_match', 'label' => 'No Match'],
            ['id' => 'pb_edge_5', 'from' => 'pb_icp_match', 'to' => 'pb_calendly', 'label' => ''],
        ],
    ];
}

function coherta_campaign_playbook_workflow_ab_test_outreach(): array
{
    $connectMessage = 'Hi {lead.first_name}. I work with B2B outreach and thought it could be relevant to connect.';
    $messageA = 'Hi {lead.first_name}. Thanks for connecting. We help teams find the right people and follow up with better timing. Should I send a short example?';
    $messageB = 'Hi {lead.first_name}. Thanks for connecting. Quick question: how are you currently finding and following up with new B2B leads?';

    return [
        'version' => 1,
        'nodes' => [
            ['id' => 'pb_opportunity', 'type' => 'opportunity', 'title' => 'Opportunity', 'x' => 520, 'y' => 120, 'config' => new stdClass()],
            ['id' => 'pb_visit', 'type' => 'visit', 'title' => 'Visit Person', 'x' => 520, 'y' => 330, 'config' => new stdClass()],
            ['id' => 'pb_wait_before_connect', 'type' => 'wait', 'title' => 'Wait 3 days', 'x' => 520, 'y' => 540, 'config' => ['duration' => 3, 'unit' => 'days', 'durationValue' => 3, 'durationUnit' => 'days']],
            ['id' => 'pb_connect', 'type' => 'connect', 'title' => 'Connect', 'x' => 520, 'y' => 750, 'config' => ['message' => $connectMessage, 'connectMessage' => $connectMessage]],
            ['id' => 'pb_if_accepted', 'type' => 'if_accepted', 'title' => 'If Accepted', 'x' => 350, 'y' => 960, 'config' => new stdClass()],
            ['id' => 'pb_ab_split', 'type' => 'ab_split', 'title' => 'Perform Split A/B Split test', 'x' => 350, 'y' => 1170, 'config' => ['aPercent' => 50, 'bPercent' => 50]],
            ['id' => 'pb_wait_a', 'type' => 'wait', 'title' => 'Wait 1 day', 'x' => 160, 'y' => 1380, 'config' => ['duration' => 1, 'unit' => 'days', 'durationValue' => 1, 'durationUnit' => 'days']],
            ['id' => 'pb_write_a', 'type' => 'write', 'title' => 'Path A DM', 'x' => 160, 'y' => 1590, 'config' => ['template' => $messageA, 'systemPrompt' => 'Keep the message short and practical.', 'system_prompt' => 'Keep the message short and practical.']],
            ['id' => 'pb_wait_b', 'type' => 'wait', 'title' => 'Wait 3 days', 'x' => 540, 'y' => 1380, 'config' => ['duration' => 3, 'unit' => 'days', 'durationValue' => 3, 'durationUnit' => 'days']],
            ['id' => 'pb_write_b', 'type' => 'write', 'title' => 'Path B DM', 'x' => 540, 'y' => 1590, 'config' => ['template' => $messageB, 'systemPrompt' => 'Keep the message curious, clear and low pressure.', 'system_prompt' => 'Keep the message curious, clear and low pressure.']],
            ['id' => 'pb_ab_merge', 'type' => 'ab_merge', 'title' => 'Merge A/B Split', 'x' => 350, 'y' => 1800, 'config' => ['splitId' => 'pb_ab_split']],
            ['id' => 'pb_monitor', 'type' => 'add_to_stakeholder_monitoring', 'title' => 'Add to Lead Monitoring', 'x' => 350, 'y' => 2010, 'config' => new stdClass()],
            ['id' => 'pb_not_accepted', 'type' => 'not_accepted', 'title' => 'Not Accepted', 'x' => 710, 'y' => 960, 'config' => new stdClass()],
            ['id' => 'pb_cleanup_wait', 'type' => 'wait', 'title' => 'Wait 35 days', 'x' => 710, 'y' => 1170, 'config' => ['duration' => 35, 'unit' => 'days', 'durationValue' => 35, 'durationUnit' => 'days']],
            ['id' => 'pb_delete_request', 'type' => 'delete_connection_request', 'title' => 'Delete Connection Request', 'x' => 710, 'y' => 1380, 'config' => new stdClass()],
        ],
        'edges' => [
            ['id' => 'pb_edge_1', 'from' => 'pb_opportunity', 'to' => 'pb_visit', 'label' => ''],
            ['id' => 'pb_edge_2', 'from' => 'pb_visit', 'to' => 'pb_wait_before_connect', 'label' => ''],
            ['id' => 'pb_edge_3', 'from' => 'pb_wait_before_connect', 'to' => 'pb_connect', 'label' => ''],
            ['id' => 'pb_edge_4', 'from' => 'pb_connect', 'to' => 'pb_if_accepted', 'label' => 'Accepted'],
            ['id' => 'pb_edge_5', 'from' => 'pb_if_accepted', 'to' => 'pb_ab_split', 'label' => 'Yes'],
            ['id' => 'pb_edge_6', 'from' => 'pb_ab_split', 'to' => 'pb_wait_a', 'label' => 'A 50%'],
            ['id' => 'pb_edge_7', 'from' => 'pb_ab_split', 'to' => 'pb_wait_b', 'label' => 'B 50%'],
            ['id' => 'pb_edge_8', 'from' => 'pb_wait_a', 'to' => 'pb_write_a', 'label' => ''],
            ['id' => 'pb_edge_9', 'from' => 'pb_write_a', 'to' => 'pb_ab_merge', 'label' => ''],
            ['id' => 'pb_edge_10', 'from' => 'pb_wait_b', 'to' => 'pb_write_b', 'label' => ''],
            ['id' => 'pb_edge_11', 'from' => 'pb_write_b', 'to' => 'pb_ab_merge', 'label' => ''],
            ['id' => 'pb_edge_12', 'from' => 'pb_ab_merge', 'to' => 'pb_monitor', 'label' => ''],
            ['id' => 'pb_edge_13', 'from' => 'pb_connect', 'to' => 'pb_not_accepted', 'label' => 'Not accepted'],
            ['id' => 'pb_edge_14', 'from' => 'pb_not_accepted', 'to' => 'pb_cleanup_wait', 'label' => ''],
            ['id' => 'pb_edge_15', 'from' => 'pb_cleanup_wait', 'to' => 'pb_delete_request', 'label' => ''],
        ],
    ];
}
function coherta_campaign_playbook_steps_bluenordix(): array
{
    return [
        [
            'title' => 'Choose the campaign in BlueNordix',
            'description' => 'Find or create the BlueNordix campaign that should receive the enriched stakeholders.',
            'image' => '/da/assets/img/bluenordix/1-BlueNordix-Campaigns.PNG',
        ],
        [
            'title' => 'Find the Campaign ID in the URL',
            'description' => 'The Campaign ID is the number in the BlueNordix URL when the campaign lead list is open.',
            'image' => '/da/assets/img/bluenordix/2-BlueNordix-Campaigns-URL.PNG',
        ],
        [
            'title' => 'Open Settings',
            'description' => 'Open Settings from the BlueNordix menu to find the integrations area.',
            'image' => '/da/assets/img/bluenordix/3-BlueNordix-Settings.PNG',
        ],
        [
            'title' => 'Open Integrations',
            'description' => 'Go to Settings and then Integrations to create or find a personal API token.',
            'image' => '/da/assets/img/bluenordix/4-BlueNordix-Settings-Integrationer.PNG',
        ],
        [
            'title' => 'Copy the API token',
            'description' => 'The token is only used in the installed campaign and is not included in the playbook itself.',
            'image' => '/da/assets/img/bluenordix/5-BlueNordix-Settings-Integrationer-Token-Redacted.PNG',
        ],
        [
            'title' => 'Install the workflow in Coherta',
            'description' => 'The workflow adds the stakeholder to monitoring, enriches the data, and then sends the person to BlueNordix.',
            'image' => '/da/assets/img/bluenordix/6-Coherta-Workflow-Stakeholder-Enrich-Send-To-BlueNordix.PNG',
        ],
        [
            'title' => 'Fill in the BlueNordix fields after installation',
            'description' => 'Open the Send to BlueNordix node and enter the URL, Campaign ID, and API token for the specific customer setup.',
            'image' => '/da/assets/img/bluenordix/7-Coherta-Workflow-URL-Campaign-API.PNG',
        ],
    ];
}

function coherta_campaign_normalize_whatsapp_workflow_config($config): array
{
    if ($config instanceof stdClass) {
        $config = (array)$config;
    }

    if (!is_array($config)) {
        $config = [];
    }

    $message = '';
    foreach (['template', 'messageTemplate', 'message', 'Message'] as $key) {
        if (array_key_exists($key, $config) && trim((string)$config[$key]) !== '') {
            $message = (string)$config[$key];
            break;
        }
    }

    $whatsAppSystemPrompt = '';
    foreach (['systemPrompt', 'whatsappSystemPrompt', 'whatsapp_system_prompt', 'WhatsAppSystemPrompt'] as $key) {
        if (array_key_exists($key, $config) && trim((string)$config[$key]) !== '') {
            $whatsAppSystemPrompt = (string)$config[$key];
            break;
        }
    }

    $config['template'] = $message;
    $config['messageTemplate'] = $message;
    $config['message'] = $message;
    $config['Message'] = $message;
    $config['systemPrompt'] = $whatsAppSystemPrompt;
    $config['whatsappSystemPrompt'] = $whatsAppSystemPrompt;
    $config['whatsapp_system_prompt'] = $whatsAppSystemPrompt;
    $config['WhatsAppSystemPrompt'] = $whatsAppSystemPrompt;
    $config['manualApproval'] = !empty($config['manualApproval']);

    return $config;
}
function coherta_campaign_sanitize_playbook_workflow(array $workflow): array
{
    if (empty($workflow['nodes']) || !is_array($workflow['nodes'])) {
        return $workflow;
    }

    $blueNordixSettingKeys = [
        'blueNordixUrl',
        'blue_nordix_url',
        'campaignId',
        'campaign_id',
        'apiToken',
        'api_token',
    ];

    foreach ($workflow['nodes'] as &$node) {
        if (!is_array($node)) {
            continue;
        }

        if (($node['type'] ?? '') === 'whatsapp_message') {
            $node['config'] = coherta_campaign_normalize_whatsapp_workflow_config($node['config'] ?? []);
            continue;
        }

        if (($node['type'] ?? '') !== 'phoner_bluenordix') {
            continue;
        }

        $config = $node['config'] ?? new stdClass();
        if ($config instanceof stdClass) {
            foreach ($blueNordixSettingKeys as $key) {
                unset($config->{$key});
            }
            $node['config'] = $config;
            continue;
        }

        if (!is_array($config)) {
            $node['config'] = new stdClass();
            continue;
        }

        foreach ($blueNordixSettingKeys as $key) {
            unset($config[$key]);
        }

        $node['config'] = $config ?: new stdClass();
    }
    unset($node);

    return $workflow;
}

function coherta_campaign_sanitize_playbook(array $playbook): array
{
    if (!empty($playbook['workflow']) && is_array($playbook['workflow'])) {
        $playbook['workflow'] = coherta_campaign_sanitize_playbook_workflow($playbook['workflow']);
    }

    return $playbook;
}

function coherta_campaign_static_playbooks(): array
{
    $playbooks = [
        [
            'slug' => 'post-likes-whatsapp',
            'title' => 'Post likes to WhatsApp',
            'heading' => 'Post likes to WhatsApp',
            'description_lines' => [
                'Use Like of Posts as the lead source.',
                'Workflow: Visit, ICP screening 80, wait 2 days, connect, wait 9 hours after accept, DM, enrich and prepare WhatsApp.',
                'WhatsApp is manual-approved and should be used after fit and enrichment.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'like_of_posts',
            'subject_label' => 'Workflow + Like of Posts',
            'install_label' => 'Includes Like of Posts source and a workflow with ICP screening before WhatsApp.',
            'workflow' => coherta_campaign_playbook_workflow_post_likes_whatsapp(),
            'lead_source' => [
                'source_type' => 'like_of_posts',
                'like_posts_mode' => 'all_posts',
                'post_like_mode' => 'all_posts',
                'posts_mode' => 'all_posts',
            ],
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'find-forbind-ring-op',
            'title' => 'Find. Connect. Call.',
            'heading' => 'Find. Connect. Call.',
            'description_lines' => [
                'Use Employee Title + Location as the lead source after installation.',
                'Workflow: Visit, wait 2 days, connect, accepted contacts go to a call list and Lead Monitoring.',
                'Not accepted requests are cleaned up after 35 days.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'employee_title_location',
            'subject_label' => 'Workflow + Employee Title setup',
            'install_label' => 'Installs the workflow. Add Employee Title + Location as the lead source after installation.',
            'workflow' => coherta_campaign_playbook_workflow_find_connect_call(),
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'ai-icp-screening-before-outreach',
            'title' => 'AI ICP screening before outreach',
            'heading' => 'AI ICP screening before outreach',
            'description_lines' => [
                'Screen every LinkedIn Search opportunity before outreach starts.',
                'Workflow: ICP screening 80, match branch, visit, wait 3 days, connect and follow up after acceptance.',
                'No-match contacts stop before they receive outreach.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'linkedin_search_url',
            'subject_label' => 'Workflow + LinkedIn Search setup',
            'install_label' => 'Installs the screening workflow. Add a LinkedIn Search URL after installation.',
            'workflow' => coherta_campaign_playbook_workflow_ai_icp_screening(),
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'linkedin-search-to-calendly-booking',
            'title' => 'LinkedIn Search to Calendly booking',
            'heading' => 'LinkedIn Search to Calendly booking',
            'description_lines' => [
                'Turn a LinkedIn Search audience into a booking workflow.',
                'Workflow: Visit, ICP screening 70, match branch and Calendly booking inside the allowed booking window.',
                'Add the LinkedIn Search URL and Calendly details after installation.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'linkedin_search_url',
            'subject_label' => 'Workflow + LinkedIn Search setup',
            'install_label' => 'Installs the workflow. Add a LinkedIn Search URL and Calendly link after installation.',
            'workflow' => coherta_campaign_playbook_workflow_linkedin_search_calendly(),
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'ab-test-outreach-flow',
            'title' => 'A/B test your outreach flow',
            'heading' => 'A/B test your outreach flow',
            'description_lines' => [
                'Test timing and message angles inside one Coherta workflow.',
                'Workflow: Visit, wait 3 days, connect, A/B split accepted contacts, merge and continue follow-up.',
                'Use the results to decide which timing and copy should become the default.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'linkedin_search_url',
            'subject_label' => 'Workflow + LinkedIn Search setup',
            'install_label' => 'Installs the A/B workflow. Add your lead source after installation.',
            'workflow' => coherta_campaign_playbook_workflow_ab_test_outreach(),
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'post-likes-linkedin-follow-up',
            'title' => 'Post likes to LinkedIn follow-up',
            'heading' => 'Post likes to LinkedIn follow-up',
            'description_lines' => [
                'Use post engagement as a warm LinkedIn follow-up signal.',
                'Workflow: Like of Posts, visit, ICP screening 80, wait 2 days, connect, wait 9 hours after accept and DM.',
                'Not accepted requests are cleaned up after 35 days.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'like_of_posts',
            'subject_label' => 'Workflow + Like of Posts',
            'install_label' => 'Includes Like of Posts source and a LinkedIn-first follow-up workflow.',
            'workflow' => coherta_campaign_playbook_workflow_post_likes_linkedin_follow_up(),
            'lead_source' => [
                'source_type' => 'like_of_posts',
                'like_posts_mode' => 'all_posts',
                'post_like_mode' => 'all_posts',
                'posts_mode' => 'all_posts',
            ],
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'bluenordix-stakeholder-overfoersel',
            'title' => 'Right stakeholders directly in BlueNordix',
            'heading' => 'C-level added straight into BlueNordix',
            'description_lines' => [
                'Workflow: Lead Monitoring, Enrich lead and Send to BlueNordix.',
                'BlueNordix URL, Campaign ID and API token are added after installation.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'auto_profile',
            'subject_label' => 'Workflow',
            'install_label' => 'Installs workflow only. Add BlueNordix URL, Campaign ID and API token after installation.',
            'workflow' => coherta_campaign_playbook_workflow_bluenordix_stakeholder_transfer(),
            'setup_steps' => coherta_campaign_playbook_steps_bluenordix(),
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'founder-saelger',
            'title' => 'Founder connects, sales follows up',
            'heading' => 'Founder connects, sales follows up',
            'description_lines' => [
                'The founder LinkedIn profile creates the warm relationships.',
                'The workflow connects, sends a short DM and adds accepted connections to monitoring.',
                'Leads are pulled automatically from the user LinkedIn profile setup.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'auto_profile',
            'subject_label' => 'Workflow + automatic lead source',
            'install_label' => 'Includes workflow. Leads are pulled automatically from the LinkedIn profile setup.',
            'workflow' => coherta_campaign_playbook_workflow_founder_sales(),
            'prepared_opportunities' => [],
        ],
        [
            'slug' => 'finn-mallery-1m-arr',
            'title' => 'How to reach $1m+ ARR',
            'heading' => 'How to reach $1m+ ARR',
            'description_lines' => [
                'Outbound flow with visit, connect, short DM and light engagement.',
                'Built for weekly volume and structured follow-up.',
                'Can be installed with prepared Opportunities or used with your own LinkedIn audience.',
            ],
            'has_workflow' => true,
            'subject_mode' => 'prepared',
            'subject_label' => 'Workflow + prepared Opportunities',
            'install_label' => 'Includes workflow and prepared Opportunities.',
            'workflow' => coherta_campaign_playbook_workflow_arr(),
            'prepared_opportunities' => [
                ['linkedin_url' => 'https://www.linkedin.com/in/example-founder/', 'first_name' => 'Example', 'last_name' => 'Founder', 'company_name' => 'Example SaaS'],
                ['linkedin_url' => 'https://www.linkedin.com/in/example-revops/', 'first_name' => 'Example', 'last_name' => 'RevOps', 'company_name' => 'Example Growth'],
                ['linkedin_url' => 'https://www.linkedin.com/in/example-salesleader/', 'first_name' => 'Example', 'last_name' => 'Sales Leader', 'company_name' => 'Example B2B'],
            ],
        ],
    ];

    return array_map('coherta_campaign_sanitize_playbook', $playbooks);
}

function coherta_campaign_find_playbook(string $slug): ?array
{
    foreach (coherta_campaign_static_playbooks() as $playbook) {
        if ($playbook['slug'] === $slug) {
            return $playbook;
        }
    }

    return null;
}
