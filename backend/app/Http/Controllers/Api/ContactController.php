<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Resend\Laravel\Facades\Resend;

class ContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'website' => ['nullable', 'string', 'max:100'],
        ]);

        if (!empty($validated['website'])) {
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
            ]);
        }

        $name = e($validated['name']);
        $email = e($validated['email']);
        $message = nl2br(e($validated['message']));
        $submittedAt = now()->format('F d, Y h:i A');

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New Contact Form Message</title>
</head>
<body style="margin:0; padding:0; background:#F3F4F6; font-family: Arial, Helvetica, sans-serif; color:#111827;">
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background:#F3F4F6; padding:32px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:640px; background:#ffffff; border:1px solid #E5E7EB; border-radius:18px; overflow:hidden; box-shadow:0 12px 30px rgba(17,24,39,0.08);">
          
          <tr>
            <td style="background:#111827; padding:28px 32px; border-bottom:4px solid #00D02B;">
              <p style="margin:0 0 8px; color:#00D02B; font-size:12px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase;">
                Xbox Gamertag Generator
              </p>
              <h1 style="margin:0; color:#ffffff; font-size:26px; line-height:1.3; font-weight:800;">
                New Contact Form Message
              </h1>
              <p style="margin:10px 0 0; color:#D1D5DB; font-size:14px; line-height:1.6;">
                Someone submitted a message from your website contact form.
              </p>
            </td>
          </tr>

          <tr>
            <td style="padding:32px;">
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                  <td style="padding:16px; background:#F8FAFC; border:1px solid #E5E7EB; border-radius:12px;">
                    <p style="margin:0 0 6px; color:#6B7280; font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase;">
                      Name
                    </p>
                    <p style="margin:0; color:#111827; font-size:16px; font-weight:700;">
                      {$name}
                    </p>
                  </td>
                </tr>

                <tr>
                  <td style="height:14px;"></td>
                </tr>

                <tr>
                  <td style="padding:16px; background:#F8FAFC; border:1px solid #E5E7EB; border-radius:12px;">
                    <p style="margin:0 0 6px; color:#6B7280; font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase;">
                      Email
                    </p>
                    <p style="margin:0; color:#111827; font-size:16px; font-weight:700;">
                      <a href="mailto:{$email}" style="color:#15803D; text-decoration:none;">{$email}</a>
                    </p>
                  </td>
                </tr>

                <tr>
                  <td style="height:14px;"></td>
                </tr>

                <tr>
                  <td style="padding:20px; background:#ffffff; border:1px solid #D1D5DB; border-radius:12px;">
                    <p style="margin:0 0 10px; color:#6B7280; font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase;">
                      Message
                    </p>
                    <div style="color:#374151; font-size:15px; line-height:1.8; word-break:break-word;">
                      {$message}
                    </div>
                  </td>
                </tr>

                <tr>
                  <td style="height:18px;"></td>
                </tr>

                <tr>
                  <td style="padding:14px 16px; background:#ECFDF5; border:1px solid #BBF7D0; border-radius:12px;">
                    <p style="margin:0; color:#166534; font-size:13px; line-height:1.6;">
                      Submitted on {$submittedAt}
                    </p>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:20px 32px; background:#F9FAFB; border-top:1px solid #E5E7EB;">
              <p style="margin:0; color:#6B7280; font-size:12px; line-height:1.6; text-align:center;">
                This email was sent from the contact form on xboxgamertaggenerator.com.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

        try {
            Resend::emails()->send([
                'from' => config('services.contact.from'),
                'to' => [config('services.contact.to')],
                'reply_to' => $validated['email'],
                'subject' => 'New Contact Form Message - Xbox Gamertag Generator',
                'html' => $html,
                'text' => "New Contact Form Message\n\nName: {$validated['name']}\nEmail: {$validated['email']}\n\nMessage:\n{$validated['message']}\n\nSubmitted on {$submittedAt}",
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Contact form email failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to send message right now. Please try again later.',
            ], 500);
        }
    }
}