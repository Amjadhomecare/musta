@extends('keen')
@section('content')

<div class="container py-4">
    <h1 class="mb-4">Home Page Management</h1>

<!-- SEO Metadata Form -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title mb-0">SEO Metadata</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('homepage.update') }}" method="POST">
            @csrf
            @method('POST')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Page Title</label>
                    <input type="text" name="metadata[title]" value="{{ $data['metadata']['title'] ?? '' }}" maxlength="70" class="form-control">
                    <small class="text-muted">Optimal for SEO: 50-60 characters</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Canonical URL</label>
                    <input type="text" name="metadata[canonical_url]" value="{{ $data['metadata']['canonical_url'] ?? '' }}" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="metadata[meta_description]" maxlength="160" rows="3" class="form-control">{{ $data['metadata']['meta_description'] ?? '' }}</textarea>
                    <small class="text-muted">Optimal for SEO: 150-160 characters</small>
                </div>
                <div class="col-12 mb-3">
                    <label for="keywords" class="form-label">Meta Keywords</label>
                    <input type="text" id="keywords" name="metadata[keywords]" value="{{ $data['metadata']['keywords'] ?? '' }}" class="form-control">
                    <small class="text-muted">Separate keywords with commas (e.g., Laravel, PHP, SEO)</small>
                </div>
            </div>

            <!-- Open Graph Settings -->
            <h3 class="mt-4 mb-3">Open Graph Settings</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">OG Title</label>
                    <input type="text" name="metadata[og_title]" value="{{ $data['metadata']['og_title'] ?? '' }}" maxlength="70" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">OG Type</label>
                    <input type="text" name="metadata[og_type]" value="{{ $data['metadata']['og_type'] ?? 'website' }}" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">OG Description</label>
                    <textarea name="metadata[og_description]" maxlength="200" rows="3" class="form-control">{{ $data['metadata']['og_description'] ?? '' }}</textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">OG Image URL</label>
                    <input type="text" name="metadata[og_image]" value="{{ $data['metadata']['og_image'] ?? '' }}" class="form-control">
                </div>
            </div>

            <!-- Twitter Card Settings -->
            <h3 class="mt-4 mb-3">Twitter Card Settings</h3>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Twitter Card Type</label>
                    <input type="text" name="metadata[twitter_card]" value="{{ $data['metadata']['twitter_card'] ?? 'summary_large_image' }}" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Twitter Title</label>
                    <input type="text" name="metadata[twitter_title]" value="{{ $data['metadata']['twitter_title'] ?? '' }}" maxlength="70" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Twitter Description</label>
                    <textarea name="metadata[twitter_description]" maxlength="200" rows="3" class="form-control">{{ $data['metadata']['twitter_description'] ?? '' }}</textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Twitter Image URL</label>
                    <input type="text" name="metadata[twitter_image]" value="{{ $data['metadata']['twitter_image'] ?? '' }}" class="form-control">
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary">Save Metadata</button>
            </div>
        </form>
    </div>
</div>


<!-- Hero Section Form -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title mb-0">Hero Section</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('homepage.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')

            <div class="row">
                <div class="col-12 mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" name="hero_section[title]" value="{{ $data['hero_section']['title'] ?? '' }}" maxlength="100" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="hero_section[description]" rows="4" class="form-control">{{ $data['hero_section']['description'] ?? '' }}</textarea>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Main Image</label>
                    <input type="file" name="main_image" class="form-control">
                    <small class="text-muted">Upload a new image to replace the existing one.</small>

                    @if(!empty($data['hero_section']['main_image_url']))
                        <div class="mt-2">
                            <img src="{{ $data['hero_section']['main_image_url'] }}" class="img-fluid" width="200">
                        </div>
                    @endif

                    <div class="mt-2">
                        <input type="text" placeholder="Image Alt Text" name="hero_section[main_image_alt]" value="{{ $data['hero_section']['main_image_alt'] ?? '' }}" maxlength="100" class="form-control">
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Secondary Image (Optional)</label>
                    <input type="file" name="secondary_image" class="form-control">
                    <small class="text-muted">Upload a new image to replace the existing one.</small>

                    @if(!empty($data['hero_section']['secondary_image_url']))
                        <div class="mt-2">
                            <img src="{{ $data['hero_section']['secondary_image_url'] }}" class="img-fluid" width="200">
                        </div>
                    @endif

                    <div class="mt-2">
                        <input type="text" placeholder="Image Alt Text" name="hero_section[secondary_image_alt]" value="{{ $data['hero_section']['secondary_image_alt'] ?? '' }}" maxlength="100" class="form-control">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary">Save Hero Section</button>
            </div>
        </form>
    </div>
</div>

<!-- Visa Section Form -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title mb-0">Visa Section</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('homepage.update') }}" method="POST">
            @csrf
            @method('POST')

            <div class="mb-3">
                <label class="form-label">Main Title</label>
                <input type="text" name="visa_section[main_title]" value="{{ $data['visa_section']['main_title'] ?? '' }}" class="form-control">
            </div>

            @for ($i = 1; $i <= 3; $i++)
                <div class="card mb-3 bg-light">
                    <div class="card-body">
                        <h3 class="card-title">Feature {{ $i }}</h3>
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="visa_section[feature_{{ $i }}_title]" value="{{ $data['visa_section']['feature_'.$i.'_title'] ?? '' }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="visa_section[feature_{{ $i }}_description]" rows="3" class="form-control">{{ $data['visa_section']['feature_'.$i.'_description'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            @endfor

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary">Save Visa Section</button>
            </div>
        </form>
    </div>
</div>

 <!-- Hire Maid Section Form -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title mb-0">Hire Maid Section</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('homepage.update') }}" method="POST">
            @csrf
            @method('POST')

            <div class="mb-3">
                <label class="form-label">Main Title</label>
                <input type="text" name="hire_maid_section[main_title]" value="{{ $data['hire_maid_section']['main_title'] ?? '' }}" class="form-control">
            </div>

            <!-- Feature 1 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Feature 1</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="hire_maid_section[feature_1_title]" value="{{ $data['hire_maid_section']['feature_1_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="hire_maid_section[feature_1_description]" rows="3" class="form-control">{{ $data['hire_maid_section']['feature_1_description'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Feature 2</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="hire_maid_section[feature_2_title]" value="{{ $data['hire_maid_section']['feature_2_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="hire_maid_section[feature_2_description]" rows="3" class="form-control">{{ $data['hire_maid_section']['feature_2_description'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Feature 3</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="hire_maid_section[feature_3_title]" value="{{ $data['hire_maid_section']['feature_3_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="hire_maid_section[feature_3_description]" rows="3" class="form-control">{{ $data['hire_maid_section']['feature_3_description'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary">Save Hire Maid Section</button>
            </div>
        </form>
    </div>
</div>


<!-- Direct Sponsorship Section Form -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title mb-0">Direct Sponsorship Section</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('homepage.update') }}" method="POST">
            @csrf
            @method('POST')

            <div class="mb-3">
                <label class="form-label">Main Title</label>
                <input type="text" name="direct_sponsorship_section[main_title]" value="{{ $data['direct_sponsorship_section']['main_title'] ?? '' }}" class="form-control">
            </div>

            <!-- Feature 1 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Feature 1</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="direct_sponsorship_section[feature_1_title]" value="{{ $data['direct_sponsorship_section']['feature_1_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="direct_sponsorship_section[feature_1_description]" rows="3" class="form-control">{{ $data['direct_sponsorship_section']['feature_1_description'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Feature 2</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="direct_sponsorship_section[feature_2_title]" value="{{ $data['direct_sponsorship_section']['feature_2_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="direct_sponsorship_section[feature_2_description]" rows="3" class="form-control">{{ $data['direct_sponsorship_section']['feature_2_description'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Feature 3</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="direct_sponsorship_section[feature_3_title]" value="{{ $data['direct_sponsorship_section']['feature_3_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="direct_sponsorship_section[feature_3_description]" rows="3" class="form-control">{{ $data['direct_sponsorship_section']['feature_3_description'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary">Save Direct Sponsorship Section</button>
            </div>
        </form>
    </div>
</div>



<!-- About Section Form -->
<div class="card mb-4">
    <div class="card-header">
        <h2 class="card-title mb-0">About Section</h2>
    </div>
    <div class="card-body">
        <form action="{{ route('homepage.update') }}" method="POST">
            @csrf
            @method('POST')

            <div class="mb-3">
                <label class="form-label">Main Title</label>
                <input type="text" name="about_section[main_title]" value="{{ $data['about_section']['main_title'] ?? '' }}" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Intro Paragraph</label>
                <textarea name="about_section[intro_paragraph]" rows="3" class="form-control">{{ $data['about_section']['intro_paragraph'] ?? '' }}</textarea>
            </div>

            <!-- Section 1 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Section 1</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="about_section[section_1_title]" value="{{ $data['about_section']['section_1_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="about_section[section_1_content]" rows="4" class="form-control">{{ $data['about_section']['section_1_content'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2 -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h3 class="card-title">Section 2</h3>
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="about_section[section_2_title]" value="{{ $data['about_section']['section_2_title'] ?? '' }}" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Content</label>
                        <textarea name="about_section[section_2_content]" rows="4" class="form-control">{{ $data['about_section']['section_2_content'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-primary">Save About Section</button>
            </div>
        </form>
    </div>
</div>


@endsection