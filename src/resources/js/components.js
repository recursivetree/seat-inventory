function workspaceCreatorPopup(container, popup){
    container.content(
        W2.html("div")
            .class("form-group")
            .content(
                W2.html("label")
                    .content("Name"),
                W2.html("input")
                    .attribute("type","text")
                    .class("form-control")
                    .attribute("placeholder","Enter the workspace's name..")
            ),
        W2.html("button")
            .class("btn btn-primary")
            .content("Create")
    )
}

function workspaceSelector(...callbacks) {
    const state = {
        workspaces: []
    }

    async function loadWorkspaces(mount) {
        const response = await fetch("/inventory/workspaces/list")
        if (response.ok){
            const data = await response.json()
            state.workspaces = data
            mount.update()
        } else {
            BoostrapToast.open("Error","Failed to load workspaces")
        }
    }

    const mount = W2.mount(state,(container, mount, state)=>{
        container.content(
            W2.html("div")
                .class("card")
                .content(
                    W2.html("div")
                        .class("card-header d-flex flex-row align-items-baseline")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Select Workspace"),
                            W2.html("button")
                                .class("btn btn-success ml-auto")
                                .content(
                                    W2.html("i").class("fas fa-plus")
                                )
                                .event("click",()=>{
                                    BootstrapPopUp.open("Create Workspace",workspaceCreatorPopup)
                                })
                        ),
                    W2.html("div")
                        .class("card-body")
                        .content(
                            W2.html("ul")
                                .class("list-group")
                                .content((container)=>{
                                    for (const workspace of state.workspaces) {
                                        container.content(
                                            W2.html("btn")
                                                .class("list-group-item list-group-item-action")
                                                .content(workspace.name)
                                                .event("click",()=>{
                                                    for (const callback of callbacks) {
                                                        callback(workspace)
                                                    }
                                                })
                                        )
                                    }
                                })
                        )
                )
        )
    })

    loadWorkspaces(mount)

    return mount
}