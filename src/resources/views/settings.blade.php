@extends('web::layouts.grids.12')

@section('title', "Settings")
@section('page_header', "Settings")


@section('full')
    <div id="main"></div>
@stop

@push('javascript')

    <script>const CSRF_TOKEN = '{{ csrf_token() }}'</script>
    <script src="@inventoryVersionedAsset('inventory/js/utils.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/select2w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/bootstrapW2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/components.js')"></script>


    <script>

        function confirmButtonComponent(text, callback) {
            const state = {
                firstStep: true
            }
            return W2.mount(state, (container, mount, state) => {
                if (state.firstStep) {
                    container.content(
                        W2.html("button")
                            .class("btn btn-danger")
                            .content(text)
                            .event("click", () => {
                                state.firstStep = false
                                mount.update()
                            })
                    )
                } else {
                    container.content(
                        W2.html("div")
                            .class("btn-group")
                            .content(
                                W2.html("button")
                                    .class("btn btn-primary")
                                    .content("Cancel")
                                    .event("click", () => {
                                        state.firstStep = true
                                        mount.update()
                                    })
                            )
                            .content(
                                W2.html("button")
                                    .class("btn btn-warning")
                                    .content("Confirm")
                                    .event("click", () => {
                                        callback()
                                        state.firstStep = true
                                        mount.update()
                                    })
                            )
                    )
                }
            })
        }

        const appState = {
            corporations: [],
            alliances: [],
            corporationSelector: null,
            allianceSelector: null,
            marketSelector: null,
            markets: [],
            currentWorkspace: null,
            newWorkspaceName: null,
            newEnableNotifications: null
        }


        async function fetchData() {
            if (appState.currentWorkspace) {
                const workspaceId = appState.currentWorkspace.id
                let response = await fetch(`{{ route("inventory.listCorporations") }}?workspace=${workspaceId}`)
                appState.corporations = await response.json()
                response = await fetch(`{{ route("inventory.listAlliances") }}?workspace=${workspaceId}`)
                appState.alliances = await response.json()
                response = await fetch(`{{ route("inventory.listMarkets") }}?workspace=${workspaceId}`)
                appState.markets = await response.json()
            }
        }

        const mount = W2.mount(appState, (container, mount, state) => {
            const hasWorkspace = state.currentWorkspace !== null

            //workspace settings
            container.contentIf(hasWorkspace, W2.html("div")
                .class("card")
                .content(
                    //title header
                    W2.html("div")
                        .class("card-header")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Workspace Settings")
                        ),
                    //card body
                    W2.html("div")
                        .class("card-body")
                        .content(
                            //name
                            W2.html("div")
                                .class("form-group")
                                .content(
                                    W2.html("label")
                                        .attribute("for", W2.getID("editWSName", true))
                                        .content("Name"),
                                    W2.html("input")
                                        .attribute("id", W2.getID("editWSName"))
                                        .class("form-control")
                                        .attribute("type", "text")
                                        .attribute("placeholder", "Enter the workspace's name...")
                                        .attribute("value", appState.newWorkspaceName || (appState.currentWorkspace ? appState.currentWorkspace.name : ""))
                                        .event("change", (e) => {
                                            appState.newWorkspaceName = e.target.value
                                        })
                                ),
                            //notifications
                            W2.html("div")
                                .class("form-check")
                                .content(
                                    W2.html("input")
                                        .attribute("id", W2.getID("editWSNotifications", true))
                                        .class("form-check-input")
                                        .attribute("type", "checkbox")
                                        .attributeIf(appState.newEnableNotifications !== null ? appState.newEnableNotifications : (appState.currentWorkspace !== null ? appState.currentWorkspace.enable_notifications === 1 : false), "checked", "checked")
                                        .event("change", (e) => {
                                            appState.newEnableNotifications = e.target.checked === true
                                        }),
                                    W2.html("label")
                                        .attribute("for", W2.getID("editWSNotifications"))
                                        .content("Notifications"),
                                ),
                            //submit
                            W2.html("div")
                                .class("form-group")
                                .content(
                                    W2.html("button")
                                        .class("btn btn-primary")
                                        .content("Save")
                                        .event("click", async () => {
                                            const data = {
                                                workspace: appState.currentWorkspace.id,
                                                name: appState.newWorkspaceName || appState.currentWorkspace.name,
                                                enableNotifications: appState.newEnableNotifications !== null ? appState.newEnableNotifications : (appState.currentWorkspace.enable_notifications === 1)
                                            }

                                            const response = await jsonPostAction("{{route("inventory.editWorkspace")}}", data)

                                            if (response.ok) {
                                                BoostrapToast.open("Success", "Successfully changed the settings")
                                            } else {
                                                BoostrapToast.open("Error", "Failed to change the settings")
                                            }

                                            //I'm too lazy
                                            location.reload()
                                        })
                                )
                        )
                )
            )

            //card for alliances
            container.contentIf(hasWorkspace, W2.html("div")
                .class("card")
                .content(
                    //title header
                    W2.html("div")
                        .class("card-header")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Alliances")
                        ),
                    //card body
                    W2.html("div")
                        .class("card-body")
                        .content(
                            W2.html("div")
                                .class("form-group d-flex flex-column w-100")
                                .content(
                                    W2.html("label")
                                        .attribute("for", W2.getID("addAlliance", true))
                                        .content("Add alliances"),
                                    select2Component({
                                        select2: {
                                            placeholder: "Select an alliance",
                                            ajax: {
                                                url: "{{ route("inventory.allianceLookup") }}"
                                            },
                                            allowClear: true,
                                        },
                                        selectionListeners: [
                                            (selection) => {
                                                state.allianceSelector = selection
                                                mount.update()
                                            }
                                        ],
                                        selection: state.allianceSelector,
                                        id: W2.getID("addAlliance")
                                    }),
                                ).contentIf(state.allianceSelector !== null,
                                W2.html("button")
                                    .class("btn btn-primary btn-block mt-2")
                                    .content("Add")
                                    .event("click", async () => {
                                        const response = await jsonPostAction("{{ route("inventory.addAlliance") }}", {
                                            alliance_id: state.allianceSelector.id,
                                            workspace: state.currentWorkspace.id
                                        })

                                        if (response.ok) {
                                            BoostrapToast.open("Success", `Successfully added ${state.allianceSelector.text}`)
                                        } else {
                                            BoostrapToast.open("Error", `Failed to add ${state.allianceSelector.text}`)
                                        }

                                        await fetchData()
                                        mount.update()
                                    })
                            ),
                            W2.html("ul")
                                .class("list-group")
                                .content(
                                    (container) => {
                                        for (const alliance of appState.alliances) {
                                            container.content(
                                                W2.html("li")
                                                    .class("list-group-item d-flex flex-row justify-content-between align-items-baseline")
                                                    .content(
                                                        W2.html("span")
                                                            .content(alliance.alliance.name),
                                                        W2.html("div")
                                                            .contentIf(!alliance.manage_members,
                                                                tooltipComponent(
                                                                    W2.html("button")
                                                                        .class("btn btn-secondary mx-1")
                                                                        .content("Add Members")
                                                                        .event("click", async () => {
                                                                            const response = await jsonPostAction("{{ route("inventory.addAllianceMembers") }}", {
                                                                                tracking_id: alliance.id
                                                                            })

                                                                            if (response.ok) {
                                                                                BoostrapToast.open("Success", `Successfully added members of ${alliance.alliance.name}`)
                                                                            } else {
                                                                                BoostrapToast.open("Error", `Failed to add members of ${alliance.alliance.name}`)
                                                                            }

                                                                            await fetchData()
                                                                            mount.update()
                                                                        }),
                                                                    "Existing and new alliance members will be added automatically.")
                                                            ).contentIf(alliance.manage_members,
                                                            tooltipComponent(
                                                                W2.html("button")
                                                                    .class("btn btn-secondary mx-1")
                                                                    .content("Remove Members")
                                                                    .event("click", async () => {
                                                                        const response = await jsonPostAction("{{ route("inventory.removeAllianceMembers") }}", {
                                                                            tracking_id: alliance.id
                                                                        })

                                                                        if (response.ok) {
                                                                            BoostrapToast.open("Success", `Successfully removed members of ${alliance.alliance.name}`)
                                                                        } else {
                                                                            BoostrapToast.open("Error", `Failed to add members of ${alliance.alliance.name}`)
                                                                        }

                                                                        await fetchData()
                                                                        mount.update()
                                                                    }),
                                                                "Automatically added corporations will be removed and no new corporations will be added in the future. Manually added corporations will stay.")
                                                        ).content(
                                                            confirmButtonComponent("Remove", async () => {
                                                                const response = await jsonPostAction("{{ route("inventory.removeAlliance") }}", {
                                                                    tracking_id: alliance.id,
                                                                })

                                                                if (response.ok) {
                                                                    BoostrapToast.open("Success", `Successfully removed ${alliance.alliance.name}`)
                                                                } else {
                                                                    BoostrapToast.open("Error", `Failed to remove ${alliance.alliance.name}`)
                                                                }

                                                                await fetchData()
                                                                mount.update()
                                                            })
                                                        )
                                                    )
                                            )
                                        }
                                    }
                                )
                        )
                )
            )

            //card for corporations
            container.contentIf(hasWorkspace, W2.html("div")
                .class("card")
                .content(
                    //title header
                    W2.html("div")
                        .class("card-header")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Corporations")
                        ),
                    //card body
                    W2.html("div")
                        .class("card-body")
                        .content(
                            W2.html("div")
                                .class("form-group d-flex flex-column w-100")
                                .content(
                                    W2.html("label")
                                        .attribute("for", W2.getID("addCorporation", true))
                                        .content("Add corporations"),
                                    select2Component({
                                        select2: {
                                            placeholder: "Select a corporation",
                                            ajax: {
                                                url: "{{ route("inventory.corporationLookup") }}"
                                            },
                                            allowClear: true,
                                        },
                                        selectionListeners: [
                                            (selection) => {
                                                state.corporationSelector = selection
                                                mount.update()
                                            }
                                        ],
                                        selection: state.corporationSelector,
                                        id: W2.getID("addCorporation")
                                    }),
                                ).contentIf(state.corporationSelector !== null,
                                W2.html("button")
                                    .class("btn btn-primary btn-block mt-2")
                                    .content("Add")
                                    .event("click", async () => {
                                        const response = await jsonPostAction("{{ route("inventory.addCorporation") }}", {
                                            corporation_id: state.corporationSelector.id,
                                            workspace: state.currentWorkspace.id
                                        })

                                        if (response.ok) {
                                            BoostrapToast.open("Success", `Successfully added ${state.corporationSelector.text}`)
                                        } else {
                                            BoostrapToast.open("Error", `Failed to add ${state.corporationSelector.text}`)
                                        }

                                        await fetchData()
                                        mount.update()
                                    })
                            ),
                            W2.html("ul")
                                .class("list-group")
                                .content(
                                    (container) => {
                                        for (const corporation of appState.corporations) {
                                            container.content(
                                                W2.html("li")
                                                    .class("list-group-item d-flex flex-row justify-content-between align-items-baseline")
                                                    .content(
                                                        W2.html("span")
                                                            .content(corporation.corporation.name),
                                                        confirmButtonComponent("Remove", async () => {
                                                            const response = await jsonPostAction("{{ route("inventory.removeCorporation") }}", {
                                                                tracking_id: corporation.id
                                                            })

                                                            if (response.ok) {
                                                                BoostrapToast.open("Success", `Successfully removed ${corporation.corporation.name}`)
                                                            } else {
                                                                BoostrapToast.open("Error", `Failed to remove ${corporation.corporation.name}`)
                                                            }

                                                            await fetchData()
                                                            mount.update()
                                                        })
                                                    )
                                            )
                                        }
                                    }
                                )
                        )
                )
            )


            //card for markets
            container.contentIf(hasWorkspace, W2.html("div")
                .class("card")
                .content(
                    //title header
                    W2.html("div")
                        .class("card-header")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Markets")
                        ),
                    //card body
                    W2.html("div")
                        .class("card-body")
                        .content(
                            W2.html("div")
                                .class("form-group d-flex flex-column w-100")
                                .content(
                                    W2.html("label")
                                        .attribute("for", W2.getID("addMarket", true))
                                        .content("Add market"),
                                    select2Component({
                                        select2: {
                                            placeholder: "Select a location",
                                            ajax: {
                                                url: "{{ route("inventory.locationLookup") }}"
                                            },
                                            allowClear: true,
                                        },
                                        selectionListeners: [
                                            (selection) => {
                                                state.marketSelector = selection
                                                mount.update()
                                            }
                                        ],
                                        selection: state.marketSelector,
                                        id: W2.getID("addMarket")
                                    }),
                                ).contentIf(state.marketSelector !== null,
                                W2.html("button")
                                    .class("btn btn-primary btn-block mt-2")
                                    .content("Add")
                                    .event("click", async () => {
                                        const response = await jsonPostAction("{{ route("inventory.addMarket") }}", {
                                            location_id: state.marketSelector.id,
                                            workspace: state.currentWorkspace.id
                                        })

                                        if (response.ok) {
                                            BoostrapToast.open("Success", `Successfully added ${state.marketSelector.text}`)
                                        } else {
                                            BoostrapToast.open("Error", `Failed to add ${state.marketSelector.text}`)
                                        }

                                        await fetchData()
                                        mount.update()
                                    })
                            ),
                            W2.html("ul")
                                .class("list-group")
                                .content(
                                    (container) => {
                                        for (const market of appState.markets) {
                                            container.content(
                                                W2.html("li")
                                                    .class("list-group-item d-flex flex-row justify-content-between align-items-baseline")
                                                    .content(
                                                        W2.html("span")
                                                            .content(market.location.name),
                                                        W2.html("div")
                                                            .content(
                                                                W2.html("button")
                                                                    .class("btn btn-secondary mx-1")
                                                                    .content("Remove Market")
                                                                    .event("click", async () => {
                                                                        const response = await jsonPostAction("{{ route("inventory.removeMarket") }}", {
                                                                            tracking_id: market.id
                                                                        })

                                                                        if (response.ok) {
                                                                            BoostrapToast.open("Success", `Successfully removed members of ${market.location.name}`)
                                                                        } else {
                                                                            BoostrapToast.open("Error", `Failed to add members of ${market.location.name}`)
                                                                        }

                                                                        await fetchData()
                                                                        mount.update()
                                                                    }))
                                                    )
                                            )
                                        }
                                    }
                                )
                        )
                )
            )
        })

        fetchData().then(() => {
            mount.update()
        })

        const rootMount = W2.mount((container, m) => {
            //workspace selection
            container.content(workspaceSelector(async (selectedWorkspace) => {
                appState.currentWorkspace = selectedWorkspace
                appState.newWorkspaceName = null
                appState.newEnableNotifications = null
                await fetchData()
                mount.update()
            }))
            container.content(mount)
        })
        rootMount.addInto("main")
    </script>
@endpush