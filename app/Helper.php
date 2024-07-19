<?php

function getSampleStatus()
{
    return [
        'DRAFT' => 'Draft',
        'SUBMITTED' => 'Submitted',
        'REJECTED' => 'Rejected',
        'RECEIVED' => 'Received',
        'PROCESSING' => 'Processing',
        'FINISHED' => 'Finished',
    ];
}

function getPriority()
{
    return [
        'HIGH' => 'High',
        'MEDIUM' => 'Medium',
        'LOW' => 'Low',
    ];
}

function getDeviceStatus()
{
    return [
        'ACTIVE' => 'Active',
        'INACTIVE' => 'Inactive',
    ];
}
