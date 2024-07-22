$(document).ready(readyFunction);

/**
 * All function execution after document is ready.
 */
function readyFunction() {
    $(document).on('click', '.openModal', clickOpenModal);
    $(document).on('click', '.addModalRow', addModalRow);
    $(document).on('click', '.removeModalRow', removeModalRow);
    $(document).on('click', '#saveBtn', postData);
    $(document).on('click', '.delete-education', deleteData);
    $(document).on('click', '.invalid-status-change', invalidStatusChangeAlert);

    // for order management
    $('.finalise-order').each(updateOrderButton);
    $('.cancel-order').each(updateOrderButton);
}

/** Show the alert message on the page
 * @param string message
 * @param string type
 */
function showAlert(message, type) {
    const alertBox = document.getElementById('educationAlert');
    const alertMessage = document.getElementById('educationAlertMessage');
    alertMessage.textContent = message;
    alertBox.classList.remove('alert-success', 'alert-danger');
    alertBox.classList.add(`alert-${type}`);
    alertBox.style.display = 'block';

    // Hide the alert after 10 seconds
    setTimeout(function () {
        alertBox.style.display = 'none';
    }, 5000);
}

/**
 * Triggered by click buttons with class="openModal", show the modal on the page.
 */
function clickOpenModal() {
    var button = $(this);
    var modal = $('#educationModal');
    var labelText = button.text();
    $('#saveBtn').removeAttr('hidden');

    var dataType = button.data('type');     // dishCategory? customisationOption? dish?
    var dataMode = button.data('mode');     // add? edit?
    var dataValue = button.data('value');   // '' if dataMode is add.
    var dataForeignValue = button.data('foreign-value');

    $('#modalLabel').text(labelText);
    $('#modalForm').html(generateModalForm(dataType));

    // view-order-details does not have a save button
    if (dataType != 'view-order-details') {
        $('#saveBtn').data('button-clicked', button);
        $('#saveBtn').data('type', dataType);
        $('#saveBtn').data('mode', dataMode);
        $('#saveBtn').data('value', dataValue);
        $('#saveBtn').data('mode', dataMode);
        $('#saveBtn').data('foreign-value', dataForeignValue);
    }

    switch (dataType) {
        case 'dishCategory':
            $.ajax({
                url: baseURL + "api/dishCategory/" + dataValue,
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    if(response.status == 200) {
                        $('#categoryName').val(response.data.CategoryName);
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function(response) {
                    showAlert(response.message, 'danger');
                }
            });
            break;

        case 'customisationOption':
            $.ajax({
                url: "/api/customisationOption/" + dataValue,
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    if (response.status == 200) {
                        $('#optionName').val(response.data.Option.OptionName);
                        $('#valuesTable tbody').empty();

                        // add rows for each data
                        response.data.Values.forEach(function(value) {
                            addModalRow(null, value.ValueName, value.ExtraPrice);
                        });
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {}
            });
            break;

        case 'dish':
            var request1 = $.ajax({
                url: baseURL + "api/getAllDishCategory/" + restaurantID,
                type: 'get',
                dataType: 'json',
            });

            var request2 = $.ajax({
                url: baseURL + "api/getAllCustomisationOptions/" + restaurantID,
                type: 'get',
                dataType: 'json',
            });

            var request3 = $.ajax({
                url: baseURL + "api/dish/" + dataValue,
                type: 'get',
                dataType: 'json',
            });

            $.when(request1, request2, request3).done(function (response1, response2, response3) {
                if (response1.status && response2.status == 200 && response3.status == 200) {
                    showAlert(`${response1.message}\n${response2.message}\n${response3.message}`, 'danger');
                } else {
                    var dishCategories = response1[0].data;
                    var allCustomisationOptions = response2[0].data;
                    var dish = response3[0].data;

                    var dishOptions = dish.Options && Array.isArray(dish.Options) ? dish.Options.map(option => option.OptionID) : [];
                    var optionsContainer = $('#optionsContainer');
                    optionsContainer.empty();

                    $('#dishName').val(dish.DishName);
                    $('#dishDescription').val(dish.Description);
                    $('#basePrice').val(dish.BasePrice);

                    dishCategories.forEach(function (category) {
                        // create a new <option> for each customisation option
                        var option = $('<option>', {
                            value: category.CategoryID,
                            text: category.CategoryName
                        });
                        // add to select
                        $('#dishCategorySelect').append(option);
                    });
                    $('#dishCategorySelect').val(dish.CategoryID);

                    allCustomisationOptions.forEach(function (option) {
                        var isChecked = dishOptions.includes(option.OptionID);
                        // create labels and checkbox
                        var checkbox = $('<input>', {
                            type: 'checkbox',
                            class: 'form-check-input',
                            id: 'option-' + option.OptionID,
                            name: 'availableOptions',
                            value: option.OptionID,
                            checked: isChecked
                        });

                        var label = $('<label>', {
                            class: 'form-check-label',
                            for: 'option-' + option.OptionID,
                            text: option.OptionName
                        });

                        var div = $('<div>', {
                            class: 'form-check'
                        }).append(checkbox).append(label);

                        optionsContainer.append(div);
                    });
                }
            });
            break;

        case 'table':
            $.ajax({
                url: baseURL + "api/table/" + dataValue,
                type: 'get',
                dataType: 'json',
                success: function(response) {
                    if(response.status == 200) {
                        $('#tableNumber').val(response.data.TableNumber);
                        $('#tableCapacity').val(response.data.Capacity);
                    } else {
                        showAlert(response.message, 'danger');
                    }

                },
                error: function(response) {
                    showAlert(response.message, 'danger');
                }
            });
            break;

        case 'qrcode':
            $('#modalLabel').text(`QR Code Table #${dataValue}`);

            // Add QR Code container to modal
            var qrcodeUrl = `${baseURL}ordering?restaurantID=${restaurantID}&tableNumber=${dataValue}`;
            $('#modalForm').html(`
                    <div id="qrcode" style="width: 100%; text-align: center;"></div>
                    <a href=${qrcodeUrl} target="_blank"><button type="button" class="btn btn-primary m-4">QR Code Link</button></a>
            `);

            // Generate QR Code
            new QRCode(document.getElementById("qrcode"), {
                text: qrcodeUrl, // URL or text to encode
                width: 256,
                height: 256
            });
            break;

        case 'view-order':
            $('#saveBtn').attr('hidden', 'hidden');
            $.ajax({
                url: `${baseURL}api/getOrderDetails/${dataValue}`,
                type: 'get',
                dataType: 'json',
                success: function (response) {
                    if(response.status == 200) {
                        var note = response.data.order.Comment;
                        if (note === '') {
                            note = 'No note leaves.';
                        }
                        $('#modalLabel').text(`Order Details #${response.data.order.OrderNumber}`);
                        $('#modalForm').html(`
                        <p><strong>Customer Name:</strong> ${response.data.order.CustomerName}</p>
                        <p><strong>Order Time:</strong> ${response.data.order.OrderTime}</p>
                        <p><strong>Table No.:</strong> ${response.data.tableNumber}</p>
                        <p><strong>Order Status:</strong> ${response.data.order.Status}</p>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Dish Name</th>
                                <th>Customisations</th>
                                <th>Amount</th>
                                <th>Price</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <strong>Note for order:</strong>
                        <p>${note}</p>
                        <strong>Total Price: A$${response.data.order.TotalPrice}</strong>
                    `);

                        var tbody = $('#modalForm tbody');

                        response.data.detailedOrder.forEach(function(item) {
                            var customisations = item.CustomisationOptions.map(function(option) {
                                return `<strong>${option.OptionName}</strong>: ${option.ValueName}`;
                            }).join(', ');

                            var row = `
                            <tr>
                                <td>${item.DishName}</td>
                                <td>${customisations}</td>
                                <td>${item.Quantity}</td>
                                <td>A$${item.UnitPrice.toFixed(2)}</td>
                            </tr>
                        `;
                            tbody.append(row);
                        });
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function (response) {
                    showAlert('Error fetching order details. Please try again.', 'danger');
                }
            });
            break;

        case 'finalise-order':
            $('#modalLabel').text('Finalise Order');
            $('#saveBtn').text('Confirm');
            break;

        case 'cancel-order':
            $('#modalLabel').text('Cancel Order');
            $('#saveBtn').text('Confirm');
            break;

        default:
            showAlert('Invalid operation.', 'danger');
            break;
    }
}

/**
 * Sepecial function for add customisation options and extra prices, where case is 'customisationOption'
 */
function addModalRow(event=null, valueName='', extraPrice='') {
    // Get the last row
    var lastRowValues = $('#valuesTable tbody tr:last').find('input');
    var isLastRowEmpty = false;''

    // Check if all places at the last row are filled.
    lastRowValues.each(function() {
        if ($(this).val().trim() === '') {
            isLastRowEmpty = true;
        }
    });

    // If the last has blanks, do not add a new row and show alert
    if (isLastRowEmpty && lastRowValues.length > 0) {
        var alert = $('#timeLimitedAlert');
        alert.text("Please fill in the last row before adding.");
        alert.show().addClass('show');
        setTimeout(function() {
            alert.hide().removeClass('show');
        }, 2500);
    } else {
        var newRow = `
            <tr>
                <td><input type="text" class="form-control" name="values[]" value="${valueName}"></td>
                <td><input type="number" class="form-control" name="prices[]" value="${extraPrice}"></td>
                <td><button type="button" class="btn btn-danger btn-sm removeModalRow">Delete</button></td>
            </tr>`;
        $('#valuesTable tbody').append(newRow);
    }
}


/**
 * Special function for case = 'customisationOption' to remove a row on the modal.
 */
function removeModalRow() {
    $(this).closest('tr').remove();
}

/**
 * Post data from Modal to the Api when clicking saveBtn (id="saveBtn")
 */
function postData() {
    var dataMode = $('#saveBtn').data('mode');
    var dataType = $('#saveBtn').data('type');
    var buttonTriggeringdModal = $('#saveBtn').data('button-clicked');
    var foreignID = $('#saveBtn').data('foreign-value');
    var ID = $('#saveBtn').data('value');
    var currentRow = buttonTriggeringdModal.closest('tr');

    var modalForm =$('#modalForm');
    if (!modalForm[0].checkValidity()) {
        // check if the form is valid.
        modalForm[0].reportValidity();
    } else {
        switch (dataType) {
            case 'dishCategory':
                var categoryName = $('#categoryName').val();
                $.ajax({
                    url: baseURL + 'api/dishCategory',
                    type: 'post',
                    data: {
                        RestaurantID: restaurantID,
                        CategoryID: ID,
                        CategoryName: categoryName,
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
                            if (dataMode == 'edit') {
                                // Update info at the row
                                currentRow.find('td:eq(0)').text(categoryName);
                                showAlert('Dish category name updated successfully.', 'success');
                            } else {
                                var ID = response.data.CategoryID;
                                var template = $('#dishCategoryRowTemplate').html();
                                var clone = $(template);

                                // Add a new row.
                                clone.find('.dishCategoryNameOnTable').text(categoryName);
                                clone.find('.row-id').val(ID);
                                clone.find('.row-foreign-id').val(foreignID);
                                clone.find('.edit-education').data('value', ID);
                                clone.find('.edit-education').data('foreign-value', foreignID);
                                clone.find('.delete-education').data('value', ID);

                                $('#dishCategoryTable').append(clone);
                                showAlert('Dish category name added successfully.', 'success');
                            }
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function () {
                        showAlert('Error updating dish category name. Please try again.' + response.message, 'danger');
                    }
                });
                break;

            case 'customisationOption':
                var optionName = $('#optionName').val();
                var values = [];
                $('#valuesTable tbody tr').each(function () {
                    var valueName = $(this).find('input[name="values[]"]').val();
                    var extraPrice = $(this).find('input[name="prices[]"]').val();
                    if (valueName && extraPrice) { // 确保输入不为空
                        values.push({
                            ValueName: valueName,
                            ExtraPrice: extraPrice
                        });
                    }
                });

                var postData = {
                    Option: {
                        OptionID: ID, // if null, it will be handled as "insert".
                        OptionName: optionName,
                        RestaurantID: restaurantID
                    },
                    Values: values
                };

                $.ajax({
                    url: baseURL + '/api/customisationOption',
                    type: 'POST',
                    dataType: 'json',
                    contentType: 'application/json',
                    data: JSON.stringify(postData),
                    success: function (response) {
                        if (response.status == 200) {
                            optionName = response.data.OptionName;
                            valuesPrices = response.data.ValuesPrices;
                            if (dataMode == 'edit') {
                                // change all info at the given row
                                currentRow.find('td:eq(0)').text(optionName);
                                currentRow.find('td:eq(1)').text(valuesPrices);
                                showAlert('Customisation Option updated successfully.', 'success');
                            } else {
                                var ID = response.data.OptionID;
                                var template = $('#customisationOptionRowTemplate').html();
                                var clone = $(template);

                                // add a new row with all info
                                clone.find('.optionNameOnTable').text(optionName);
                                clone.find('.valuesPricesOnTable').text(valuesPrices);
                                clone.find('.row-id').val(ID);
                                clone.find('.row-foreign-id').val(foreignID);
                                clone.find('.edit-education').data('value', ID);
                                clone.find('.edit-education').data('foreign-value', foreignID);
                                clone.find('.delete-education').data('value', ID);

                                $('#customisationOptionTable').append(clone);
                                showAlert('Customisation Option added successfully.', 'success');
                            }
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', error);
                        alert('Failed to save customisation option: ' + error);
                    }
                });
                break;

            case 'dish':
                var dishName = $('#dishName').val();
                var dishDescription = $('#dishDescription').val();
                var basePrice = $('#basePrice').val();
                var categoryID = $('#dishCategorySelect').find(":selected").val();

                var selectedOptions = [];
                $('#optionsContainer input[type="checkbox"]:checked').each(function () {
                    selectedOptions.push($(this).val());  // Select all selected value in the checkbox.
                });

                $.ajax({
                    url: baseURL + 'api/dish',
                    type: 'post',
                    data: {
                        RestaurantID: restaurantID,
                        CategoryID: categoryID,
                        DishID: ID,
                        Description: dishDescription,
                        DishName: dishName,
                        BasePrice: basePrice,
                        OptionIDs: selectedOptions.length > 0 ? selectedOptions : []
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
                            categoryName = response.data.CategoryName;
                            dishName = response.data.DishName;
                            dishDescription = response.data.Description;
                            basePrice = response.data.BasePrice;
                            availableOptions = response.data.OptionName;

                            if (dataMode == 'edit') {
                                // update all the data at that row
                                currentRow.find('td:eq(0)').text(categoryName);
                                currentRow.find('td:eq(1)').text(dishName);
                                currentRow.find('td:eq(2)').text(truncateText(dishDescription));
                                currentRow.find('td:eq(3)').text(basePrice);
                                currentRow.find('td:eq(4)').text(availableOptions);
                                showAlert('Dish updated successfully.', 'success');
                            } else {
                                var ID = response.data.DishID;
                                var template = $('#dishRowTemplate').html();
                                var clone = $(template);

                                // Insert a new row
                                clone.find('.categoryNameOnTable').text(categoryName);
                                clone.find('.dishNameOnTable').text(dishName);
                                clone.find('.descriptionOnTable').text(dishDescription);
                                clone.find('.basePriceOnTable').text(basePrice);
                                clone.find('.optionNameOnTable').text(availableOptions);
                                clone.find('.row-id').val(ID);
                                clone.find('.row-foreign-id').val(foreignID);
                                clone.find('.edit-education').data('value', ID);
                                clone.find('.edit-education').data('foreign-value', foreignID);
                                clone.find('.delete-education').data('value', ID);

                                $('#dishTable').append(clone);
                                showAlert('Dishadded successfully.', 'success');
                            }
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function () {
                        showAlert('Error updating dish. Please try again.', 'danger');
                    }
                });
                break;

            case 'table':
                var tableNumber = $('#tableNumber').val();
                var tableCapacity = $('#tableCapacity').val();

                $.ajax({
                    url: baseURL + 'api/table',
                    type: 'post',
                    data: {
                        RestaurantID: restaurantID,
                        TableID: ID,
                        TableNumber: tableNumber,
                        Capacity: tableCapacity
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.status == 200) {
                            if (dataMode == 'edit') {
                                // Update all the info at the row
                                currentRow.find('td:eq(0)').text(tableNumber);
                                currentRow.find('td:eq(1)').text(tableCapacity);
                                showAlert('Table updated successfully.', 'success');
                            } else {
                                var ID = response.data.TableID;
                                var template = $('#tableRowTemplate').html();
                                var clone = $(template);

                                // Insert a new row
                                clone.find('.tableNumberOnTable').text(tableNumber);
                                clone.find('.tableCapacityOnTable').text(tableCapacity);
                                clone.find('.row-id').val(ID);
                                clone.find('.row-foreign-id').val(foreignID);
                                clone.find('.edit-education').data('value', ID);
                                clone.find('.edit-education').data('foreign-value', foreignID);
                                clone.find('.delete-education').data('value', ID);

                                $('#tableTable').append(clone);
                                showAlert('Table added successfully.', 'success');
                            }
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function () {
                        showAlert('Error updating table. Please try again.', 'danger');
                    }
                });
                break;

            case 'finalise-order':
                $.ajax({
                    url: baseURL + 'api/changeOrderStatus',
                    type: 'post',
                    dataType: 'json',
                    data: {OrderID: ID, Action: 'finalise'},
                    success: function (response) {

                        // Update the status text at this row
                        currentRow.find('td:eq(6)').text('Completed');
                        currentRow.find('button').each(function () {
                            $(this).data('order-status', 'Completed');
                        });

                        // Update the button colour
                        currentRow.find('button').each(updateOrderButton);
                        showAlert(response.message, 'success');
                    },
                    error: function () {
                        showAlert('Fail to finalise order. Please try again.', 'danger');
                    }
                })
                break;

            case 'cancel-order':
                $.ajax({
                    url: baseURL + 'api/changeOrderStatus',
                    type: 'post',
                    dataType: 'json',
                    data: {OrderID: ID, Action: 'cancel'},
                    success: function (response) {

                        // Update the status text at this row
                        currentRow.find('td:eq(6)').text('Concelled');
                        currentRow.find('button').each(function () {
                            $(this).data('order-status', 'Cancelled');
                        });

                        // Update the button colour
                        currentRow.find('button').each(updateOrderButton);
                        showAlert(response.message, 'success');
                    },
                    error: function () {
                        showAlert('Fail to cancel order. Please try again.', 'danger');
                    }
                })
                break;
            default:
                showAlert('Invalid operation.', 'danger');
                break;
        }
        // When everything is done, close the modal.
        $('#educationModal').modal('hide');
    }
}

/**
 * Delete dishCategory, customisationOption, dish row from the database and view at the page of restaurant_view
 */
function deleteData() {
    var button = $(this);
    var dataValue = button.data('value');
    var dataType = button.data('type');
    const confirmation = confirm('Are you sure you want to delete this education?');

    if (confirmation) {
        $.ajax({
            url: baseURL + "api/" + dataType + "/delete/" + dataValue,
            type: 'get',
            dataType: 'json',
            success: function(response) {
                if (response.status == 200) {
                    var currentRow = button.closest('tr');
                    showAlert('Data deleted successfully.', 'success');
                    currentRow.remove();
                } else {
                    showAlert(response.message, 'danger');
                }

            },
            error: function() {
                console.error('Data deleting education:', response.error);
                showAlert('Error deleting data. Please try again.', 'danger');
            }
        });
    }
}

/**
 * Generate form and add it to modal.
 * @param type data-type in the html element, add corresponding elements based on the type
 * @returns {string}
 */
function generateModalForm(type) {
    if (type == 'dishCategory') {
        return `
        <div class="mb-3">
            <label for="categoryName" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="categoryName" name="categoryName" required>
        </div>
    `;
    } else if (type == 'customisationOption') {
        return `
        <div class="mb-3">
            <label for="optionName" class="form-label">Option Name</label>
            <input type="text" class="form-control" id="optionName" name="optionName" required>
        </div>
        <div class="mb-3">
            <label class="form-label"></label>
            <table class="table" id="valuesTable">
                <thead>
                    <tr>
                        <th>Value</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <button type="button" class="btn btn-primary addModalRow">Add Row</button>
        </div>
    `;
    } else if (type == 'dish') {
        return `
        <div class="mb-3">
            <label for="dishName" class="form-label">Dish Name</label>
            <input type="text" class="form-control" id="dishName" name="dishName" required>
        </div>
        <div class="mb-3">
            <label for="dishCategory" class="form-label">In Category </label>
            <select name="dishCategories" id="dishCategorySelect"></select>
        </div>
        <div class="mb-3">
            <label for="dishDescription" class="form-label">Description</label>
            <textarea class="form-control" id="dishDescription" name="dishDescription" required></textarea>
        </div>
        <div class="mb-3">
            <label for="basePrice" class="form-label">Base Price</label>
            <input type="number" class="form-control" id="basePrice" name="basePrice" required>
        </div>
        <div class="mb-3">
            <label for="availableOptions" class="form-label">Available Options</label>
            <div id="optionsContainer"></div>
        </div>
    `;
    } else if (type == 'table') {
        return `
        <div class="mb-3">
            <label for="TableNumber" class="form-label">Table Number</label>
            <input type="text" class="form-control" id="tableNumber" name="tableNumber" required>
        </div>
        <div class="mb-3">
            <label for="TableCapacity" class="form-label">Table Capacity</label>
            <input type="number" class="form-control" id="tableCapacity" name="tableCapacity" required>
        </div>
        `;
    }  else if (type == 'finalise-order') {
        return '<p class="form-label">Confirm to finalise this order</p>'
    } else if (type == 'cancel-order') {
        return '<p class="form-label">Confirm to cancel this order</p>'
    } else {
        return '';
    }
}

/**
 * Special Function on the view of restaurant order management. Update the colour and class of "finalise" and "cancel"
 * buttons to show different colours and statuses.
 */
function updateOrderButton () {
    var $this = $(this);

    // If the button is view-order, do nothing.
    if ($this.data('type') != 'view-order') {

        // If the order status is 'Pending', either "finalise" or "cancel" should be light colours.
        if ($this.data('order-status') === 'Pending') {
            $this.addClass('openModal');
            $this.removeClass('btn-light');

            // 'finalise' should be green and 'cancel' should be yellow
            if ($this.data('type') == 'finalise-order') {
                $this.addClass('btn-success')
            } else {
                $this.addClass('btn-warning')
            }
            $this.attr('data-bs-toggle', 'modal');
            $this.attr('data-bs-target', '#educationModal');
        } else {

            // If not pending, both of them are light colours.
            $this.removeClass('openModal');
            $this.removeClass('btn-success');
            $this.removeClass('btn-warning');
            $this.addClass('btn-light');
            $this.removeAttr('data-bs-toggle');
            $this.removeAttr('data-bs-target');
            $this.addClass('invalid-status-change');
        }
    }
}

/**
 * When clicking a light colour button (order is finalised or cancelled), show alert.
 */
function invalidStatusChangeAlert() {
    var dataType = $(this).data('type');
    var orderStatus = $(this).data('order-status');

    if (dataType == 'finalise-order') {
        var action = 'finalised'
    } else {
        var action = 'canceled'
    }
    showAlert(`${orderStatus} Order cannot be ${action}.`, 'danger');
}

/**
 * Truncate text if too long, and add '...' at the end.
 * @param text the text to be processed.
 * @param maxChars The maximum of the char number.
 * @returns {string} Original text if length below maxChars. If too long, it will be truncated.
 */
function truncateText(text, maxChars = 30) {
    if (text.length > maxChars) {
        text = text.substring(0, maxChars + 1);
        let lastSpace = text.lastIndexOf(' ');
        if (lastSpace !== -1) {
            text = text.substring(0, lastSpace);
        } else {
            text = text.substring(0, maxChars);
        }
        text += '...';
    }
    return text;
}
