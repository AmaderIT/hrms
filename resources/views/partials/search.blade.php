<div id="kt_quick_search" class="offcanvas offcanvas-right p-10">
    <!--begin::Header-->
    <div class="offcanvas-header d-flex align-items-center justify-content-between mb-5">
        <h3 class="font-weight-bold m-0">Search
            <small class="text-muted font-size-sm ml-2">files, reports, members</small></h3>
        <a href="#" class="btn btn-xs btn-icon btn-light btn-hover-primary" id="kt_quick_search_close">
            <i class="ki ki-close icon-xs text-muted"></i>
        </a>
    </div>
    <!--end::Header-->
    <!--begin::Content-->
    <div class="offcanvas-content">
        <!--begin::Container-->
        <div class="quick-search quick-search-offcanvas quick-search-has-result" id="kt_quick_search_offcanvas">
            <!--begin::Form-->
            <form method="get" class="quick-search-form border-bottom pt-5 pb-1">
                <div class="input-group">
                    <div class="input-group-prepend">
								<span class="input-group-text">
									<span class="svg-icon svg-icon-lg">
										<!--begin::Svg Icon | path:assets/media/svg/icons/General/Search.svg-->
										<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
											<g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
												<rect x="0" y="0" width="24" height="24" />
												<path d="M14.2928932,16.7071068 C13.9023689,16.3165825 13.9023689,15.6834175 14.2928932,15.2928932 C14.6834175,14.9023689 15.3165825,14.9023689 15.7071068,15.2928932 L19.7071068,19.2928932 C20.0976311,19.6834175 20.0976311,20.3165825 19.7071068,20.7071068 C19.3165825,21.0976311 18.6834175,21.0976311 18.2928932,20.7071068 L14.2928932,16.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" />
												<path d="M11,16 C13.7614237,16 16,13.7614237 16,11 C16,8.23857625 13.7614237,6 11,6 C8.23857625,6 6,8.23857625 6,11 C6,13.7614237 8.23857625,16 11,16 Z M11,18 C7.13400675,18 4,14.8659932 4,11 C4,7.13400675 7.13400675,4 11,4 C14.8659932,4 18,7.13400675 18,11 C18,14.8659932 14.8659932,18 11,18 Z" fill="#000000" fill-rule="nonzero" />
											</g>
										</svg>
                                        <!--end::Svg Icon-->
									</span>
								</span>
                    </div>
                    <input type="text" class="form-control" placeholder="Search..." />
                    <div class="input-group-append">
								<span class="input-group-text">
									<i class="quick-search-close ki ki-close icon-sm text-muted"></i>
								</span>
                    </div>
                </div>
            </form>
            <!--end::Form-->
            <!--begin::Wrapper-->
            <div class="quick-search-wrapper pt-5">
                <div class="quick-search-result">
                    <!--begin::Message-->
                    <div class="text-muted d-none">No record found</div>
                    <!--end::Message-->
                    <!--begin::Section-->
                    <div class="font-size-sm text-primary font-weight-bolder text-uppercase mb-2">Documents</div>
                    <div class="mb-10">
                        <!--begin::Item-->
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 bg-transparent flex-shrink-0">
                                <img src="assets/media/svg/files/doc.svg" alt="" />
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">AirPlus Requirements</a>
                                <span class="font-size-sm font-weight-bold text-muted">by Grog John</span>
                            </div>
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 bg-transparent flex-shrink-0">
                                <img src="assets/media/svg/files/pdf.svg" alt="" />
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">TechNav Documentation</a>
                                <span class="font-size-sm font-weight-bold text-muted">by Mary Broun</span>
                            </div>
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 bg-transparent flex-shrink-0">
                                <img src="assets/media/svg/files/xml.svg" alt="" />
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">All Framework Docs</a>
                                <span class="font-size-sm font-weight-bold text-muted">by Nick Stone</span>
                            </div>
                        </div>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 bg-transparent flex-shrink-0">
                                <img src="assets/media/svg/files/csv.svg" alt="" />
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">Finance &amp; Accounting Reports</a>
                                <span class="font-size-sm font-weight-bold text-muted">by Jhon Larson</span>
                            </div>
                        </div>
                        <!--end::Item-->
                    </div>
                    <!--end::Section-->
                    <!--begin::Section-->
                    <div class="font-size-sm text-primary font-weight-bolder text-uppercase mb-2">Members</div>
                    <div class="mb-10">
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label" style="background-image:url('assets/media/users/150-2.jpg')"></div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">Milena Gibson</a>
                                <span class="font-size-sm font-weight-bold text-muted">UI Designer</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label" style="background-image:url('assets/media/users/150-14.jpg')"></div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">Stefan JohnStefan</a>
                                <span class="font-size-sm font-weight-bold text-muted">Marketing Manager</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label" style="background-image:url('assets/media/users/150-2.jpg')"></div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">Anna Strong</a>
                                <span class="font-size-sm font-weight-bold text-muted">Software Developer</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label" style="background-image:url('assets/media/users/150-11.jpg')"></div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">Nick Bold</a>
                                <span class="font-size-sm font-weight-bold text-muted">Project Coordinator</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->
                    <!--begin::Section-->
                    <div class="font-size-sm text-primary font-weight-bolder text-uppercase mb-2">Files</div>
                    <div class="mb-10">
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label">
                                    <i class="flaticon-psd text-primary"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">79 PSD files generated</a>
                                <span class="font-size-sm font-weight-bold text-muted">by Grog John</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label">
                                    <i class="flaticon2-supermarket text-warning"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">$2900 worth products sold</a>
                                <span class="font-size-sm font-weight-bold text-muted">Total 234 items</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label">
                                    <i class="flaticon-safe-shield-protection text-info"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">4 New items submitted</a>
                                <span class="font-size-sm font-weight-bold text-muted">Marketing Manager</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center flex-grow-1 mb-2">
                            <div class="symbol symbol-30 flex-shrink-0">
                                <div class="symbol-label">
                                    <i class="flaticon-safe-shield-protection text-warning"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column ml-3 mt-2 mb-2">
                                <a href="#" class="font-weight-bold text-dark text-hover-primary">4 New items submitted</a>
                                <span class="font-size-sm font-weight-bold text-muted">Marketing Manager</span>
                            </div>
                        </div>
                    </div>
                    <!--end::Section-->
                </div>
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Content-->
</div>
