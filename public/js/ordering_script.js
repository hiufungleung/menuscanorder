let shoppingCart = [];
let orderPrice = 0;

$(document).ready(readyFunction);

/**
 * All event listener when document is ready.
 */
function readyFunction() {
    adjustMainContentHeight();
    $(window).resize(adjustMainContentHeight);
    $(document).on('click', '.openModal', clickOpenModal);
    $(document).on('click', '#saveBtn', postData);
    $(document).on('click', '.btn-number', quantityModifier);
    $(document).on('change', '.input-number', inputNumberChange);
    $(document).on('keydown', '.input-number', inputNumberKeyboardChange);
}

/** Show the alert message on the page
 * @param string message
 * @param string type
 */
function showAlert(message, type, duration=5000) {
    const alertBox = document.getElementById('educationAlert');
    const alertMessage = document.getElementById('educationAlertMessage');
    alertMessage.textContent = message;
    alertBox.classList.remove('alert-success', 'alert-danger');
    alertBox.classList.add(`alert-${type}`);
    alertBox.style.display = 'block';

    // Hide the alert after 10 seconds by default
    setTimeout(function () {
        alertBox.style.display = 'none';
    }, duration);
}

/**
 * Adjust the <main> main content's height to maintain the height of <body> is 100% at all time
 */
function adjustMainContentHeight() {
    var headerHeight = $('header').outerHeight(); // Get the out height of <header>, including padding and border.
    var footerHeight = $('footer').outerHeight(); // Get the out height of <footer>

    // Calculate the height of <main>
    var mainHeight = $(window).height() - headerHeight - footerHeight;

    // set the css style
    $('main').css('height', mainHeight + 'px');
}

/**
 * Triggered by click buttons with class="openModal", show the modal on the page.
 */
function clickOpenModal() {
    var button = $(this);
    var modalTarget = button.data('bs-target');
    var labelText = button.get(0).innerText;
    var modalBody = $('#modalForm');
    modalBody.empty();
    $('#modal-price').empty();

    var dataType = button.data('type');
    var dataMode = button.data('mode');
    var dataValue = button.data('value');
    var dataForeignValue = button.data('foreign-value');

    $('#saveBtn').text(labelText);
    $('#saveBtn').removeAttr('hidden');
    $('#modalForm').html(generateModalForm(dataType));
    $('#saveBtn').data('button-clicked', button);
    $('#saveBtn').data('type', dataType);
    $('#saveBtn').data('mode', dataMode);
    $('#saveBtn').data('value', dataValue);
    $('#saveBtn').data('mode', dataMode);
    $('#saveBtn').data('foreign-value', dataForeignValue);

    switch (dataType) {
        case 'add-dish':
            $.ajax({
                url: baseURL + 'api/getDishDetails/' + dataValue,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#dishName').text(response.data.DishName);
                    $('#modalLabel').text(response.data.DishName);
                    $('#dishDescription').text(response.data.Description);
                    $('#modal-price').text(`Price: A$${response.data.BasePrice}`);
                    $('#modal-price').data('base-price', response.data.BasePrice);

                    var tableContainer = $('#tableContainer');
                    tableContainer.empty();

                    if (Object.keys(response.data.AvailableOptions).length > 0) {
                        var table = `
                        <table class="table" id="valuesTable">
                            <thead>
                                <tr>
                                    <th>Options</th>
                                    <th>Values</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>`;

                        tableContainer.append(table);
                        var tbody = $('#valuesTable tbody');
                        tbody.empty();

                        $.each(response.data.AvailableOptions, function(optionName, values) {
                            var row = $('<tr></tr>');
                            row.append('<td>' + optionName + '</td>');

                            var select = $('<select class="form-select" data-option-name="' + optionName + '"></select>');
                            $.each(values, function(index, value) {
                                var optionText = value.ValueName + ' (+A$' + value.ExtraPrice + ')';
                                var optionElement = $('<option></option>')
                                    .attr('value', value.ValueID)
                                    .attr('data-value-name', value.ValueName)
                                    .attr('data-extra-price', value.ExtraPrice)
                                    .text(optionText);

                                // Check if ValueName contains 'Regular'
                                if (value.ValueName.includes('Regular')) {
                                    optionElement.attr('selected', 'selected'); // set it to be selected.
                                }
                                select.append(optionElement);
                            });

                            row.append($('<td></td>').append(select));
                            tbody.append(row);
                        })
                        $('#valuesTable select').change(updateDishModalPriceDisplay);
                        $('.input-number').off('change').on('change',updateDishModalPriceDisplay);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching dish details:', error);
                }
            });
            break;

        case 'shopping-cart':
            $('#modalLabel').text('Cart');
            $('#saveBtn').attr('hidden', 'hidden');
            updateTotalPriceDisplay();

            if (shoppingCart.length == 0) {
                modalBody.append('<h3>Your cart is empty.</h3>');
            } else {

                var table = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Dish</th>
                            <th>Amount</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>`;

                modalBody.append(table);
                var tbody = modalBody.find('tbody');

                shoppingCart.forEach(function (item, index) {
                    var optionsText = item.SelectedValuesName.filter(function(valueName) {
                        return !valueName.includes('Regular');
                    }).join(', ');

                    var row = `
                    <tr data-index="${index}">
                        <td>${item.DishName}<p class="text-black-50 m-0" style="font-size: 15px;">${optionsText}</p></td>
                        <td>${getInputGroup(item.Quantity)}</td>
                        <td>A$${item.UnitPrice.toFixed(2)}</td>
                        <td><button type="button" class="btn btn-warning btn-m remove-item">Remove</button></td>
                    </tr>
                    `;
                    tbody.append(row);
                });

                $('.input-number').off('change').on('change', function () {
                    var newQuantity = parseInt($(this).val());
                    var rowIndex = $(this).closest('tr').data('index');
                    shoppingCart[rowIndex].Quantity = newQuantity;
                    updateTotalPriceDisplay();
                });

                $(document).on('click', '.remove-item', function () {
                    var row = $(this).closest('tr');
                    var rowIndex = row.data('index');
                    shoppingCart.splice(rowIndex, 1); // 刪除相應項目
                    row.remove(); // 移除表格行
                    updateTotalPriceDisplay();
                });
            }
            break;

        case 'send-order':
            $('#modalLabel').text('Confirm');
            var modalForm = $('#modalForm');
            modalForm.empty();

            if (shoppingCart.length == 0) {
                modalForm.append('<h3>Your cart is empty.</h3>');
            } else {
                var table = `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Dish</th>
                            <th>Amount</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>`;

                modalForm.append(table);
                var tbody = modalForm.find('tbody');

                // Get all the dishes with customisation option from the shopping cart.
                shoppingCart.forEach(function (item) {
                    var optionsText = item.SelectedValuesName.filter(function(valueName) {
                        return !valueName.includes('Regular');
                    }).join(', ');

                    var row = `
                    <tr>
                        <td>${item.DishName}<p class="text-black-50 m-0" style="font-size: 15px;">${optionsText}</p></td>
                        <td>${item.Quantity}</td>
                        <td>A$${item.UnitPrice}</td>
                    </tr>
                    `;
                    tbody.append(row);
                });

                modalForm.append(`
                    <div class="mb-3">
                        <label for="customerName" class="form-label">How may we call you?</label>
                        <input type="text" class="form-control" id="customerName" name="customerName" 
                        placeholder="Name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="orderComment" class="form-label">Any note for this order</label>
                        <textarea class="form-control" id="orderComment" name="orderComment" 
                        placeholder="Any sepecial requirement for dishes..."></textarea>
                    </div>
                `);

                $.ajax({
                    url: `${baseURL}api/calculateDishesPrice`,
                    type: 'post',
                    data: {Dishes: shoppingCart},
                    success: function (response) {
                        if (response.status == 200) {
                            orderPrice = response.data.TotalPrice;
                            $('.total-price').text(`Total Price: A$${orderPrice}`);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function (response) {
                        showAlert(response.message, 'danger');
                    }
                });
            }
            break;

        default:
            showAlert('Invalid operation.', 'danger');
            break;
    }
}


/**
 * Post data from Modal to the Api when clicking saveBtn (id="saveBtn")
 */
function postData() {
    var dataType = $('#saveBtn').data('type');
    var modalForm =$('#modalForm');
    if (!modalForm[0].checkValidity()) {
        modalForm[0].reportValidity();
    } else {
        switch (dataType) {
            case 'add-dish':

                // Get all the dish info
                var dishID = $('#saveBtn').data('value');
                var quantity = parseInt($('.input-number').val()) || 1;
                var dishName = $('#dishName').text();
                var selectedValuesID = [];
                var selectedValuesName = [];

                // get all the option selected for this dish
                $('#valuesTable select').each(function () {
                    var selectedOption = $(this).find('option:selected');
                    var valueID = selectedOption.val();
                    var valueName = selectedOption.data('value-name');
                    selectedValuesID.push(valueID);
                    selectedValuesName.push(valueName);
                });

                // Pack data for api
                var dish = {
                    DishID: dishID,
                    Quantity: 1,
                    SelectedValuesID: selectedValuesID,
                    SelectedValuesName: selectedValuesName
                };

                $.ajax({
                    url: `${baseURL}api/calculateDishesPrice`,
                    type: 'post',
                    data: {Dishes: [dish]},
                    success: function (response) {
                        if (response.status == 200) {
                            response.data.Dishes.forEach(function (item) {
                                var existingDish = shoppingCart.find(d => d.DishID == item.DishID && arraysEqual(d.SelectedValuesID, dish.SelectedValuesID));
                                if (existingDish) {
                                    existingDish.Quantity += quantity;
                                } else {
                                    var newDish = {
                                        DishID: dish.DishID,
                                        DishName: dishName,
                                        Quantity: quantity,
                                        UnitPrice: item.UnitPrice,
                                        SelectedValuesID: dish.SelectedValuesID,
                                        SelectedValuesName: dish.SelectedValuesName
                                    };
                                    shoppingCart.push(newDish);
                                }

                                // Update the total price in the shopping cart
                                orderPrice += response.data.TotalPrice;
                                $('#footer-price').text(`Total Price: A$${(orderPrice * quantity).toFixed(2)}`);
                            });

                            showAlert('Added to order.', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function (response) {
                        showAlert(response.message, 'danger');
                    }
                });
                break;

            case 'send-order':
                if (shoppingCart.length == 0) {
                    showAlert('Please add a dish to cart.', 'danger');
                } else {
                    // Get the customer name and order note.
                    var customerName = $('#customerName').val();
                    var orderComment = $('#orderComment').val();

                    $.ajax({
                        url: `${baseURL}api/sendOrder`,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            RestaurantID: restaurantID,
                            TableID: tableID,
                            Comment: orderComment,
                            CustomerName: customerName,
                            Dishes: shoppingCart
                        },
                        success: function (response) {
                            if (response.status === 200) {
                                showAlert('Order sent successfully', 'success', 2000);
                                var orderID = response.data.OrderID;
                                var orderNumber = response.data.OrderNumber;
                                var restaurantID = response.data.RestaurantID;
                                setTimeout(function () {
                                    window.location.href = `${baseURL}orderstatus?restaurantID=${restaurantID}&orderNumber=${orderNumber}`;
                                }, 2000);
                            } else {
                                showAlert(response.message, 'danger',);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error('Error sending order:', error);
                            showAlert('Error sending order. Please try again.', 'danger');
                        }
                    });
                }

                break;

            default:
                showAlert('Invalid operation.', 'danger');
                break;
        }

        $('#educationModal').modal('hide');
    }
}

/**
 * Generate form and add it to modal.
 * @param type data-type in the html element, add corresponding elements based on the type
 * @returns {string}
 */
function generateModalForm(type) {

    // when click add for a dish on ordering page
    if (type == 'add-dish') {
        return `
        <div class="mb-3">
            <h5 id="dishName" style="display: none;">Dish Name</h5>
            <p class="" id="dishDescription">Description</p>
        </div>
        <div class="mb-3" id="tableContainer">
        </div>
        ${getInputGroup()}
    `;
    } else if (type == 'send-order') {
        return '';
    }
    else {
        return '';
    }
}

/**
 * Get a input group with [-, <number>, +]
 * @param quantity the value in <input>.val()
 * @returns {string} the html text of the input group
 */
function getInputGroup(quantity=1) {
    return `
    <div class="input-group" style="width: 105px;">
          <span class="input-group-btn">
              <button type="button" class="btn btn-light btn-number"  data-type="minus" data-field="quant[2]">
                <span class="glyphicon glyphicon-minus"></span>-
              </button>
          </span>
          <input type="text" name="quant[2]" class="form-control input-number" value="${quantity}" name="quantity">
          <span class="input-group-btn">
              <button type="button" class="btn btn-light btn-number" data-type="plus" data-field="quant[2]">
                  <span class="glyphicon glyphicon-plus"></span>+
              </button>
          </span>
      </div>
    `;
}

/**
 * When clicking '-' or '+', <input>.val() minus or plus one
 * @param e click event
 */
function quantityModifier(e) {
    e.preventDefault();

    var fieldName = $(this).data('field');
    var type = $(this).data('type');
    var input = $(this).closest('.input-group').find('.input-number');
    var currentVal = parseInt(input.val());

    if (!isNaN(currentVal)) {
        if (type == 'minus') {
            if (currentVal > 1) {
                input.val(currentVal - 1).change();
            }
        } else if (type == 'plus') {
            input.val(currentVal + 1).change();
        }
    } else {
        input.val(1);
    }
}

/**
 * Add a constraint for input value when <input>.val() changes.
 */
function inputNumberChange() {
    var minValue = parseInt($(this).attr('min')) || 1;
    var maxValue = parseInt($(this).attr('max')) || 999;
    var valueCurrent = parseInt($(this).val());

    var name = $(this).attr('name');
    if (valueCurrent >= minValue) {
        $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
    } else {
        showAlert('Sorry, the minimum value was reached', 'danger');
        $(this).val(minValue);
    }
    if (valueCurrent <= maxValue) {
        $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
    } else {
        showAlert('Sorry, the maximum value was reached', 'danger');
        $(this).val(maxValue);
    }
}

/**
 * Add a key listener constraint, If the keyboard input is not number, nothing happens.
 * @param e the key press event.
 */
function inputNumberKeyboardChange (e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
        // Allow: Ctrl/cmd+A
        (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: Ctrl/cmd+C
        (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: Ctrl/cmd+X
        (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
        // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        // let it happen, don't do anything
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
}

/**
 * Check whether two arrays are identical.
 * @param arr1 The first array to be compared.
 * @param arr2 The second array to be compared.
 * @returns {boolean} True if two arrays are identical.
 */
function arraysEqual(arr1, arr2) {
    if (arr1.length !== arr2.length) return false;
    for (var i = 0; i < arr1.length; i++) {
        if (arr1[i] !== arr2[i]) return false;
    }
    return true;
}

/**
 * Calculate the total price in the shoppingCart.
 * @returns {int} Total price in the shoppingCart.
 */
function calculateTotalPrice() {
    return shoppingCart.reduce(function (accumulator, item) {
        return accumulator + (item.UnitPrice * item.Quantity);
    }, 0);
}


/**
 * Update the modal price when clicking a dish and change options or quantity.
 */
function updateDishModalPriceDisplay() {
    var basePrice = parseFloat($('#modal-price').data('base-price') || 0);
    var totalPrice = basePrice;
    $('#valuesTable select').each(function() {
        var selectedOption = $(this).find('option:selected');
        var extraPrice = parseFloat(selectedOption.data('extra-price')) || 0;
        totalPrice += extraPrice;
    });

    var quantity = parseInt($('.input-number').val()) || 1;
    totalPrice *= quantity;

    $('#modal-price').text('Total Price: A$' + totalPrice.toFixed(2));
}

/**
 * Update the total price when shoppingCart updates.
 */
function updateTotalPriceDisplay() {
    var totalPrice = calculateTotalPrice();
    orderPrice = totalPrice;
    var priceText = `Total Price: A$${totalPrice.toFixed(2)}`;
    $('#footer-price').text(priceText);
    $('#modal-price').text(priceText);
}