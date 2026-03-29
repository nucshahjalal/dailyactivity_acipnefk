<?php

namespace App\Http\Controllers\Admin;

use App\Mail\SendMail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class sendMailController extends Controller
{
    public function sendMail(Request $request)
    {
        $mailData = [
    'title' => 'Daily Activity',
    'body' => "Dear Supervisor,

        We kindly request you to take a moment to rate and review your employee’s performance on the Daily Activity Supervisor Panel.

        Please login to the portal using the credentials below:
        Login URL: https://acipanel.com/dailyactivity/
        UserID: EmployeeID
        Password: EmployeeID

        After logging in, go to the following link to rate and review your employee:

        Rating & Review: https://acipanel.com/dailyactivity/rating_review/on/my_supervision

        Your feedback is highly appreciated and plays a key role in supporting employee growth and accountability.

        Thank you for your time and cooperation.

        Best regards,
        ACI Motors Team",
        ];

        $recipients = [
          'alimran@aci-bd.com',
            'al.Abdullah@aci-bd.com',
            'almasud@aci-bd.com',
            'Arif.rahman@aci-bd.com',
            'Ashif@aci-bd.com',
            'dipsarker@aci-bd.com',
            'hossain.forhad@aci-bd.com',
            'option@aci-bd.com',
            'irtiza@aci-bd.com',
            'Jabed@aci-bd.com',
            'sakif@aci-bd.com',
            'maksudur@aci-bd.com',
            'radowanul@aci-bd.com',
            'samiul.alim@aci-bd.com',
            'amannan@aci-bd.com',
            'mamamun@aci-bd.com',
            'arafat.hossain@aci-bd.com',
            'asif@aci-bd.com',
            'azam@aci-bd.com',
            'Bashirul@aci-bd.com',
            'Emrul@aci-bd.com',
            'ferdous@aci-bd.com',
            'arabi@aci-bd.com',
            'jakaria@aci-bd.com',
            'khabib@aci-bd.com',
            'khairul@aci-bd.com',
            'mahfuz.rahman@aci-bd.com',
            'muntazir@aci-bd.com',
            'rokon.sarker@aci-bd.com',
            'salimsarker@aci-bd.com',
            'zahidul@aci-bd.com',
            'mirajul@aci-bd.com',
            'ms.islam@aci-bd.com',
            'jakirhossain@aci-bd.com',
            'kabir.hussain@aci-bd.com',
            'muntakim@aci-bd.com',
            'Samsuzzaman@aci-bd.com',
            'tanim@aci-bd.com',
            'johan@aci-bd.com',
            'yeasir@aci-bd.com',
            'zahidul@aci-bd.com',
            'Jahiduzzaman@aci-bd.com',
            'osmanwasiu@aci-bd.com',
            'acimotorsapp@gmail.com',
            'alim@aci-bd.com',
            'Ashif@aci-bd.com',
            'al.abdullah@aci-bd.com',
            'Iftekharul.motor@aci-bd.com',
            'minhajulislam@aci-bd.com',
            'newaz@aci-bd.com',
            'rahman.masud@aci-bd.com',
            'Tanvir.motor@aci-bd.com',
            'sibat@aci-bd.com',
            'Rizwan@aci-bd.com',
            'zakir.sarker@aci-bd.com',
            'hasan.kamrul@aci-bd.com',
            'kowshik@aci-bd.com',
            'ahsan.Kamrul@aci-bd.com',
            'Hossain.sabbir@aci-bd.com',
            'amin.Shahriar@aci-bd.com',
            'shafiul.motor@aci-bd.com',
            'Nayeem.mirza@aci-bd.com',
            'al.abdullah@aci-bd.com',
            'rizwan@aci-bd.com'
        ];


        foreach ($recipients as $recipient) {
            Mail::to($recipient)->send(new SendMail($mailData));
        }

        return response()->json(['message' => 'Emails sent successfully.']);
    }
}
