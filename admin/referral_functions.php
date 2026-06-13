<?php

require_once __DIR__.'/db.php';

function updateReferralCounts($userId)
{
    $db = db();

    $directStmt = $db->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE referred_by=?
    ");

    $directStmt->execute([$userId]);

    $direct = (int)$directStmt->fetchColumn();

    $indirectStmt = $db->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE referred_by IN (
            SELECT id
            FROM users
            WHERE referred_by=?
        )
    ");

    $indirectStmt->execute([$userId]);

    $indirect = (int)$indirectStmt->fetchColumn();

    $bonus = ($direct + $indirect) * 0.5;

    $update = $db->prepare("
        UPDATE users
        SET
            direct_referrals=?,
            indirect_referrals=?,
            referral_bonus_percent=?
        WHERE id=?
    ");

    $update->execute([
        $direct,
        $indirect,
        $bonus,
        $userId
    ]);
}