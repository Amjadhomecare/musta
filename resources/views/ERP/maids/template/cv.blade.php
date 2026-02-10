<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        table {
            border: 3px solid black !important;
        }

        table th,
        table td {
            border: 3px solid black !important;
        }

        h5,
        h6 {
            font-weight: bold;
            color: #0d6efd;
        }

        .text-muted {
            font-weight: bold;
        }

        /* Hide the print button and adjust content to fit on one page for printing */
        @media print {
            .no-print {
                display: none; /* Hide the print button */
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
                -webkit-print-color-adjust: exact; /* Ensure colors are printed correctly */
            }

            .container {
                max-width: 100% !important;
                width: 100%;
                padding: 0;
            }

            /* Fit content on one page */
            * {
                font-size: 12pt; /* Adjust font size */
                zoom: 90%; /* Ensures the content fits within the page */
            }

            @page {
                size: A4;
                margin: 10mm; /* Adjust page margins if needed */
            }
        }

        /* Ensure container is wide enough but keeps spacing in view mode */
        .container {
            max-width: 1000px; /* Adjust for view */
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <!-- Print Button -->
        <div class="d-flex justify-content-end mb-4 no-print">
            <button class="btn btn-primary" onclick="window.print()">Print CV</button>
        </div>

        <div class="border border-secondary border-3 p-3">
            <div class="row mb-4">
                <div class="col text-center">
                    <img src="{{ env('cv_head') }}" class="img-fluid"  alt="gg"/>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-4">
                    <img src="{{$cv->img}}" class="rounded-circle border border-secondery border-3" style="width: 176px; height: 180px;" />
                </div>

                <div class="text-center">
                    <h5 class="" style="border: 3px solid black; border-radius: 8px; padding: 10px;">
                        {{$cv->name}}
                    </h5>
                </div>
            </div>
            <table class="table table-bordered text-center">
                <tbody>
                    <tr>
                        <td>Ref No.</td>
                        <td>{{$cv->id}}</td>
                        <td>الرقم الجامعي</td>
                    </tr>
                    <tr>
                        <td>Job Description</td>
                        <td>House maid</td>
                        <td>الوظيفة</td>
                    </tr>
                    <tr>
                        <td>Contract period</td>
                        <td>2 YEARS</td>
                        <td>مدة العقد</td>
                    </tr>
                </tbody>
            </table>

            <div class="text-center">
                <h5 class="fs-4 badge text-bg-primary text-wrap text-center ">APPLICATION</h5>
            </div>
            <table class="table table-bordered text-center">
                <tbody>
                    <tr>
                        <td>Nationality</td>
                        <td>{{$cv->nationality}}</td>
                        <td>الجنسية</td>
                    </tr>
                    <tr>
                        <td>Religion</td>
                        <td>{{$cv->religion}}</td>
                        <td>الديانة</td>
                    </tr>
                    <tr>
                        <td>Marital Status</td>
                        <td>{{$cv->marital_status}}</td>
                        <td>الحالة الاجتماعية</td>
                    </tr>
                    <tr>
                        <td>No. of Children</td>
                        <td>{{$cv->child}}</td>
                        <td>عدد الأطفال</td>
                    </tr>
                </tbody>
            </table>

            <div class="text-center">
                <h5 class="fs-4 badge text-bg-primary text-wrap">البيانات الشخصية</h5>
            </div>
            <table class="table table-bordered text-center">
                <tbody>
                    <tr>
                        <td>Sex</td>
                        <td>Female</td>
                        <td>الجنس</td>
                    </tr>
                    <tr>
                        <td>Age</td>
                        <td>{{$cv->age}} YEARS</td>
                        <td>العمر</td>
                    </tr>
                    <tr>
                        <td>Height</td>
                        <td>{{$cv->height}} CM</td>
                        <td>الطول</td>
                    </tr>
                    <tr>
                        <td>Weight</td>
                        <td>{{$cv->weight}} KG</td>
                        <td>الوزن</td>
                    </tr>
                    <tr>
                        <td>Qualification</td>
                        <td>{{$cv->education}}</td>
                        <td>المؤهل العلمي</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <h5 class="fs-4 badge text-bg-primary text-wrap">Language / اللغات</h5>
            </div>
            <table class="table table-bordered text-center">
                <thead>
                    <tr>
                        <th>English <br> الانجليزية</th>
                        <th>Arabic <br> العربية</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{$cv->lang_english}}</td>
                        <td>{{$cv->lang_arabic}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <h5 class="fs-4 badge text-bg-primary text-wrap">Work Experience</h5>
            </div>
            <table class="table table-bordered text-center">
                <tbody>
                    <tr>
                        <td>Years of Exp.<br>عدد سنوات الخبرة</td>
                        <td>Country of Exp.<br>بلد الخبرة</td>
                    </tr>
                    <tr>
                        <td>{{$cv->period_country}}</td>
                        <td>{{$cv->exp_country}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <h5 class="fs-4 badge text-bg-primary text-wrap">Field of Experience</h5>
            </div>

            <table class="table table-bordered text-center">
                <tbody>
                    <tr>
                        <td>Baby Sitter</td>
                        <td>Cooking</td>
                        <td>Cleaning</td>
                        <td>Washing</td>
                    </tr>
                    <tr>
                        <td>&#9745;</td>
                        <td>{{$cv->cooking}}</td>
                        <td>&#9745;</td>
                        <td>&#9745;</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center">
                <h5 class="fs-4 badge text-bg-primary text-wrap">Notes</h5>
            </div>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td>Notes: {{$cv->note}}</td>
                    </tr>
                </tbody>
            </table>

            <div class="text-center">
                <h5 class="fs-4 badge text-bg-primary text-wrap text-center">Application Photo</h5>
            </div>
            <div class="text-center border border-secondery border-9">
                <img src="{{$cv->img2}}" class="img-fluid" style="height: 270px; width: 260px;" />
            </div>

            <div class="text-center mt-4">
                {{$cv->video_link}}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>
