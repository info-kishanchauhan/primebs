<?php
// Force Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
 * =========================
 * EMAIL BODY EXTRACT (FIXED)
 * =========================
 * - Sirf 1 copy aayegi (no more "same text twice in bubble").
 * - Prefer text/plain. Agar nahi mila to stripped text/html.
 */

// ✅ main getter
function getBody($inbox, $email_number) {
    $structure = imap_fetchstructure($inbox, $email_number);
    if (!$structure) return '';

    // state container for best bodies we find
    $state = [
        'plain' => null, // best
        'html'  => null, // fallback
    ];

    collectBestBody($inbox, $email_number, $structure, '', $state);

    // prefer plain first, else fallback to html
    $body = $state['plain'] !== null ? $state['plain'] : ($state['html'] ?? '');

    // remove soft line breaks from quoted-printable ("=\r\n")
    $body = preg_replace("/=\r\n/", '', $body);

    // trim + normalize UTF-8
    $body = trim($body);
    $body = mb_convert_encoding($body, 'UTF-8', 'UTF-8');

    return $body;
}

// recursive walker
function collectBestBody($inbox, $email_number, $structure, $partNumber, array &$state) {

    // multipart? -> dive in children
    if (isset($structure->parts) && is_array($structure->parts) && count($structure->parts) > 0) {
        foreach ($structure->parts as $i => $part) {
            $newPartNo = $partNumber ? ($partNumber . '.' . ($i+1)) : (string)($i+1);
            collectBestBody($inbox, $email_number, $part, $newPartNo, $state);
        }
        return;
    }

    // leaf part
    $subtype  = strtolower($structure->subtype ?? '');
    $type     = (int)($structure->type ?? 0); // 0 == TYPETEXT
    $encoding = (int)($structure->encoding ?? 0);

    // only care about text/plain or text/html
    if ($type !== TYPETEXT) {
        return;
    }
    if ($subtype !== 'plain' && $subtype !== 'html') {
        return;
    }

    // fetch body for this specific part (or "1" if top)
    $rawBody = imap_fetchbody($inbox, $email_number, $partNumber ?: 1);

    // decode by transfer encoding
    if ($encoding === 3) {          // BASE64
        $rawBody = base64_decode($rawBody);
    } elseif ($encoding === 4) {    // QUOTED-PRINTABLE
        $rawBody = quoted_printable_decode($rawBody);
    }

    if ($subtype === 'plain') {
        // save first plain we see (don't overwrite if already set)
        if ($state['plain'] === null) {
            $state['plain'] = trim($rawBody);
        }
    } elseif ($subtype === 'html') {
        // fallback html -> convert to readable text (preserve <br>, <p> as newlines)
        if ($state['html'] === null) {
            $tmp = preg_replace('/<\s*br\s*\/?>/i', "\n", $rawBody);
            $tmp = preg_replace('/<\s*\/p\s*>/i', "\n\n", $tmp);
            $tmp = strip_tags($tmp);
            $state['html'] = trim($tmp);
        }
    }
}

/* ==== PDA ATTACHMENT HELPERS (ADD-ON) ==== */
if (!function_exists('pda_decode_mime_header')) {
    function pda_decode_mime_header($str) {
        if (function_exists('iconv_mime_decode')) {
            $d = @iconv_mime_decode($str, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
            if ($d !== false) return $d;
        }
        if (function_exists('mb_decode_mimeheader')) {
            $d = @mb_decode_mimeheader($str);
            if ($d !== false && $d !== '') return $d;
        }
        return $str;
    }
}

/**
 * Recursively walk the message structure and save attachments.
 * Returns array of [ ['file_name'=>..., 'file_path'=>...], ... ]
 */
if (!function_exists('pda_saveAttachmentsRecursive')) {
    function pda_saveAttachmentsRecursive($inbox, $email_number, $structure, $partNoPrefix = '', $uploadDir = '/tmp', $maxBytes = 20*1024*1024) {
        $saved = [];

        if (isset($structure->parts) && is_array($structure->parts)) {
            foreach ($structure->parts as $i => $part) {
                $partNo = $partNoPrefix ? ($partNoPrefix . '.' . ($i + 1)) : (string)($i + 1);

                // Nested parts recurse
                if (isset($part->parts) && is_array($part->parts)) {
                    $saved = array_merge(
                        $saved,
                        pda_saveAttachmentsRecursive($inbox, $email_number, $part, $partNo, $uploadDir, $maxBytes)
                    );
                    continue;
                }

                // Detect attachment by filename/name parameters
                $isAttachment = false;
                $filename     = '';

                if (!empty($part->dparameters)) {
                    foreach ($part->dparameters as $p) {
                        $attr = strtolower($p->attribute ?? '');
                        if ($attr === 'filename' || $attr === 'filename*') {
                            $isAttachment = true;
                            $filename     = pda_decode_mime_header($p->value);
                        }
                    }
                }
                if (!$isAttachment && !empty($part->parameters)) {
                    foreach ($part->parameters as $p) {
                        $attr = strtolower($p->attribute ?? '');
                        if ($attr === 'name' || $attr === 'name*') {
                            $isAttachment = true;
                            $filename     = pda_decode_mime_header($p->value);
                        }
                    }
                }

                if ($isAttachment && $filename !== '') {
                    $raw = imap_fetchbody($inbox, $email_number, $partNo);
                    $enc = (int)($part->encoding ?? 0);
                    if     ($enc === 3) $raw = base64_decode($raw);
                    elseif ($enc === 4) $raw = quoted_printable_decode($raw);

                    // Size guard (20 MB)
                    if ($maxBytes > 0 && strlen($raw) > $maxBytes) continue;

                    if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

                    $safeBase = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
                    $safeName = time() . '_' . bin2hex(random_bytes(3)) . '_' . $safeBase;
                    $filePath = rtrim($uploadDir, '/') . '/' . $safeName;

                    if (@file_put_contents($filePath, $raw) !== false) {
                        $saved[] = ['file_name' => $safeBase, 'file_path' => $filePath];
                    }
                }
            }
        }

        return $saved;
    }
}
/* ==== /PDA ATTACHMENT HELPERS ==== */


try {
    // ✅ CLI safe base path
    $basePath = '/home/primebackstage/htdocs/www.primebackstage.in';

    require_once($basePath . '/public/phpmailernew/PHPMailer.php');
    require_once($basePath . '/public/phpmailernew/SMTP.php');
    require_once($basePath . '/public/phpmailernew/Exception.php');

    // Connect to IMAP server
    $hostname = '{imap.hostinger.com:993/imap/ssl}INBOX';
    $username = 'support@primedigitalarena.in';
    $password = 'Razvi@78692786';

    $inbox = imap_open($hostname, $username, $password);
    if (!$inbox) {
        throw new Exception('Cannot connect to mail server: ' . imap_last_error());
    }

    $emails = imap_search($inbox, 'UNSEEN');

    if ($emails) {
        rsort($emails); // latest first
        $db = new mysqli("localhost", "primebs", "mJYwFtL4QNuzvPuz2PNZ", "primebs");

        if ($db->connect_error) {
            throw new Exception('Database connection failed: ' . $db->connect_error());
        }

        // 🛡️ Admin/our-domain denylist
        $DENYLIST = [
            'support@primedigitalarena.in',
            'no-reply@primedigitalarena.in',
            'info@primedigitalarena.in',
            'admin@primedigitalarena.in',
        ];
        $OUR_DOMAIN_REGEX = '/@primedigitalarena\.in$/i';

        // ✅ trusted domains (parent / wildcard)
        $TRUSTED_DOMAINS = [
            '@primedigitalarena.in',
            '@believedigital.com',
            '@parentco.in',
        ];

        foreach ($emails as $email_number) {

            // --- Sender info ---
            $h = imap_headerinfo($inbox, $email_number);
            $fromEmail = '';
            if (!empty($h->from) && isset($h->from[0]->mailbox, $h->from[0]->host)) {
                $fromEmail = strtolower($h->from[0]->mailbox . '@' . $h->from[0]->host);
            }
            $fromEmailNorm = strtolower(trim($fromEmail)); // normalized once here

            // ❌ skip if this is literally us / system / internal no-reply
            if ($fromEmailNorm &&
                (in_array($fromEmailNorm, $DENYLIST, true) || preg_match($OUR_DOMAIN_REGEX, $fromEmailNorm))) {
                imap_setflag_full($inbox, $email_number, "\\Seen");
                echo "⏭ Skipped (admin/our-domain): $fromEmailNorm\n";
                continue;
            }

            // --- Subject & body ---
            $overview = imap_fetch_overview($inbox, $email_number, 0)[0];

            $body_clean = getBody($inbox, $email_number);

            // ✅ Clean user reply from Gmail / Outlook / phones (strip quoted history etc)
            $patterns = [
                '/^>+/m',                                // ">"
                '/-----\s*Original Message\s*-----/i',   // Outlook thread header
                '/From:\s.+\nSent:\s.+\nTo:\s.+/i',      // Outlook forward block
                '/^\s*Sent from my.*/mi',                // "Sent from my iPhone"
                '/^\s*--\s*$/m',                         // signature delimiter
            ];

            foreach ($patterns as $p) {
                $parts = preg_split($p, $body_clean);
                if (!empty($parts[0])) {
                    $body_clean = $parts[0];
                }
            }

            $body_clean = trim($body_clean);
            $body_clean = mb_convert_encoding($body_clean, 'UTF-8', 'UTF-8'); // UTF-8 safe

            $subject = $overview->subject ?? '';
            $subject = trim(str_ireplace(['Re:', 'Fwd:', 'Fw:'], '', $subject));

            // ====== ticket id detect from subject ======
            if (preg_match('/#(\d+)/', $subject, $matches)) {
                $ticket_id = (int)$matches[1];

                if (!empty($body_clean)) {

                    // ---------- SMART DEDUPE (avoid double insert in DB) ----------

                    // normalize body: collapse all whitespace to single space
                    $norm_body  = trim(preg_replace('/\s+/', ' ', $body_clean));
                    $norm_email = strtolower(trim($fromEmailNorm));

                    // Pull last few replies for SAME ticket + SAME sender email
                    $dedupeStmt = $db->prepare("
                        SELECT id, reply_text, replied_at
                        FROM tbl_ticket_replies
                        WHERE ticket_id = ?
                          AND LOWER(TRIM(original_from_email)) = ?
                        ORDER BY replied_at DESC
                        LIMIT 3
                    ");
                    $dedupeStmt->bind_param("is", $ticket_id, $norm_email);
                    $dedupeStmt->execute();
                    $dedupeRes = $dedupeStmt->get_result();

                    $shouldInsert = true;
                    while ($rowDup = $dedupeRes->fetch_assoc()) {
                        $old_body_norm = trim(preg_replace('/\s+/', ' ', $rowDup['reply_text'] ?? ''));
                        $old_time      = strtotime($rowDup['replied_at'] ?? '');

                        // check identical text
                        if ($old_body_norm === $norm_body) {
                            // and time diff < 5 min => treat as duplicate
                            if ($old_time && (time() - $old_time) < 300) {
                                $shouldInsert = false;
                                break;
                            }
                        }
                    }
                    $dedupeStmt->close();

                    if ($shouldInsert) {

                        /*
                         * STEP A: get ticket owner info
                         */
                        $ticketRowQ = $db->prepare("
                            SELECT t.user_id,
                                   s.email AS user_email
                            FROM tbl_support_tickets t
                            LEFT JOIN tbl_staff s ON s.id = t.user_id
                            WHERE t.id = ?
                        ");
                        $ticketRowQ->bind_param("i", $ticket_id);
                        $ticketRowQ->execute();
                        $ticketRowRes = $ticketRowQ->get_result()->fetch_assoc();
                        $ticketRowQ->close();

                        $ticketOwnerId    = (int)($ticketRowRes['user_id'] ?? 0);
                        $ticketOwnerEmail = strtolower(trim($ticketRowRes['user_email'] ?? ''));

                        /*
                         * STEP B: choose which user_id to save in tbl_ticket_replies
                         * default 9999 = external/partner
                         */
                        $user_id = 9999;

                        // same email as ticket owner -> treat as that user
                        if ($ticketOwnerEmail !== '' && $fromEmailNorm === $ticketOwnerEmail) {
                            $user_id = $ticketOwnerId;
                        } else {
                            // OR trusted domain (believe, parentco etc) -> also map to ticket owner
                            foreach ($TRUSTED_DOMAINS as $dom) {
                                $domLower = strtolower($dom);
                                $len      = strlen($domLower);
                                if ($len > 0 && substr($fromEmailNorm, -$len) === $domLower) {
                                    $user_id = $ticketOwnerId;
                                    break;
                                }
                            }
                        }

                        /*
                         * STEP C: INSERT reply
                         * also store original_from_email for display bubble ("arvlogsin@gmail.com")
                         */
                        $stmt = $db->prepare("
                            INSERT INTO tbl_ticket_replies (
                                ticket_id,
                                user_id,
                                original_from_email,
                                reply_text,
                                replied_at
                            )
                            VALUES (?, ?, ?, ?, NOW())
                        ");
                        if ($stmt === false) {
                            throw new Exception('Prepare failed: ' . $db->error);
                        }

                        $stmt->bind_param("iiss", $ticket_id, $user_id, $fromEmailNorm, $body_clean);
                        $stmt->execute();

                        $reply_id = $stmt->insert_id;
                        $stmt->close();

                        /*
                         * STEP D: SAVE EMAIL ATTACHMENTS
                         */
                        $__UPLOAD_DIR = $basePath . '/public/uploads/tickets/';
                        $__structure  = imap_fetchstructure($inbox, $email_number);

                        if ($__structure) {
                            $__saved = pda_saveAttachmentsRecursive(
                                $inbox,
                                $email_number,
                                $__structure,
                                '',
                                $__UPLOAD_DIR,
                                20 * 1024 * 1024
                            );

                            if (!empty($__saved)) {
                                foreach ($__saved as $__att) {
                                    $__safeName = basename($__att['file_name']);
                                    $__filePath = $__att['file_path'];

                                    $___ins = $db->prepare("
                                        INSERT INTO tbl_ticket_attachments (
                                            ticket_id,
                                            reply_id,
                                            user_id,
                                            file_path,
                                            uploaded_at
                                        )
                                        VALUES (?, ?, ?, ?, NOW())
                                    ");
                                    if ($___ins === false) {
                                        throw new Exception('Attachment insert prepare failed: ' . $db->error);
                                    }
                                    $___ins->bind_param("iiis", $ticket_id, $reply_id, $user_id, $__filePath);
                                    $___ins->execute();
                                    $___ins->close();

                                    echo "📎 Saved attachment: " . $__safeName . PHP_EOL;
                                }
                            }
                        }

                        /*
                         * STEP E: mark ticket updated / reopen if closed
                         */
                        $statusCheck = $db->query("SELECT status FROM tbl_support_tickets WHERE id = $ticket_id");
                        if ($statusCheck && $row = $statusCheck->fetch_assoc()) {
                            $currentStatus = $row['status'];

                            if ($currentStatus === 'Closed') {
                                // Only reopen closed
                                $db->query("UPDATE tbl_support_tickets SET status = 'Open', new_reply = 1 WHERE id = $ticket_id");
                                $db->query("INSERT INTO tbl_ticket_history (ticket_id, status, updated_at)
                                            VALUES ($ticket_id, 'Reopened via email', NOW())");
                            } else {
                                // Just "new reply" flag
                                $db->query("UPDATE tbl_support_tickets SET new_reply = 1 WHERE id = $ticket_id");
                            }
                        }

                        echo "✅ Inserted Reply for Ticket #$ticket_id (from $fromEmailNorm, stored user_id=$user_id)\n";

                    } else {
                        echo "⚡ Skipped duplicate-style reply for Ticket #$ticket_id (from $fromEmailNorm)\n";
                    }
                }
            }

            // mark as seen so we don't process same email again later
            imap_setflag_full($inbox, $email_number, "\\Seen");
        }

        $db->close();
    } else {
        echo "ℹ️ No new emails found.\n";
    }

    imap_close($inbox);

    echo "✅ Fetch Ticket Replies Script Completed.\n";

} catch (Exception $e) {
    echo "❗ Error: " . $e->getMessage() . "\n";
}
?>
