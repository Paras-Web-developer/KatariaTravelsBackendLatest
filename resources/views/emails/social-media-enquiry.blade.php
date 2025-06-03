<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            margin: auto;
        }

        h1 {
            color: #4CAF50;
            text-align: center;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        a:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- <div
            style="display: flex; justify-content: center; padding: 20px 0; align-items: center; background-color: #FFC93B">
            <img src="{{ asset('images/logo.jpeg') }}" alt="FOMOEdge logo" style="height: 100px; width: 200px;">
        </div> --}}
        <h1>Hi, {{ $SocialMediaEnquiry->full_name ?? '' }}</h1>

        <p style="font-weight: bold; font-style: italic">Thank you for Submitting your travel enquiry with Kataria Tours
            and Travel!.</p>
        <p style="font-style: italic">Here are the details of your travel enquiry:</h2>

        <h3>Enquiry Details::</h3>
        <p><strong>Enquiry full_name:</strong> {{ $SocialMediaEnquiry->status ?? '' }}</p>
        <p><strong>Enquiry email:</strong> {{ $SocialMediaEnquiry->email->id ?? '' }}</p>
        <p><strong>Enquiry phone:</strong> {{ $SocialMediaEnquiry->phone ?? '' }}</p>
        <P><strong>Enquiry departure_city:</strong> {{ $SocialMediaEnquiry->departure_city ?? '' }}</P>
        <p><strong>Enquiry destination_city:</strong> {{ $SocialMediaEnquiry->destination_city ?? '' }}</p>
        <p><strong>Enquiry travel_date:</strong> {{ $SocialMediaEnquiry->travel_dates ?? '' }}</p>
        <p><strong>Enquiry return_date:</strong> {{ $SocialMediaEnquiry->return_date ?? '' }}</p>
        <p><strong>Enquiry no_of_passengers:</strong> {{ $SocialMediaEnquiry->no_of_passengers ?? '' }}</p>

        <p style="font-weight: bold; font-style: italic">We are reviewing your request and will get back to you shortly.
            Should you have any additional questions, feel free to contact us directly here.</p>

        <p>Warm regards,</p>
        <p>the Team of Kataria Tours and Travel</p>
        <div>
            <img src="{{ asset('images/logo.jpeg') }}" alt="Kataria Tours and Travel logo"
                style="height: 100px; width: 200px;">
        </div>
        {{-- <div
            style="display: flex; justify-content: center; padding: 20px 0; align-items: left; background-color: #FFC93B">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Kataria Tours and Travel logo"
                style="height: 100px; width: 200px;">
        </div> --}}


        <h3>Company Contact Information:</h3>
        <p><strong>Company Name:</strong> Kataria Tours and Travel</p>
        <p><strong>Company Email:</strong> katariatravel@gmail.com</p>
        <p><strong>Company Phone:</strong> +1 1888-678-1201</p>
        <p><strong>Company Address:</strong>UNIT 12, 4550 EBENEZER RD BRAMPTON ONTARIO 16P 1H4, Canada</p>
        <p><strong>Website:</strong> https://www.katariatravel.com</p>
        <p style="font-style: italic">If you have any questions or need further assistance, please don't hesitate to
            contact us. We are here to help you!</p>

    </div>
</body>

</html>
