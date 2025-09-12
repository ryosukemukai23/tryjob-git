document.addEventListener('DOMContentLoaded', function() {
  // 面接日時の追加機能
  const interviewDateGroup = document.getElementById('interview-date-group');
  if (interviewDateGroup) {
    const inputInner = interviewDateGroup.querySelector('.is-input_inner');
    if (inputInner) {
      const addButton = inputInner.querySelector('.add button');
      if (addButton) {
        addButton.addEventListener('click', function() {
          // Create a new date picker div
          const newDatePicker = document.createElement('div');
          newDatePicker.className = 'date-picker';
          
          // Create the year select element
          const yearSelect = document.createElement('select');
          yearSelect.id = 'interview-year-2';
          const yearOption = document.createElement('option');
          yearOption.textContent = '日付';
          yearSelect.appendChild(yearOption);
          
          // Create the month select element
          const monthSelect = document.createElement('select');
          monthSelect.id = 'interview-month-2';
          const monthOption = document.createElement('option');
          monthOption.textContent = '時';
          monthSelect.appendChild(monthOption);
          
          // Create the day select element
          const daySelect = document.createElement('select');
          daySelect.id = 'interview-day-2';
          const dayOption = document.createElement('option');
          dayOption.textContent = '分';
          daySelect.appendChild(dayOption);
          
          // Append all select elements to the new date picker
          newDatePicker.appendChild(yearSelect);
          newDatePicker.appendChild(monthSelect);
          newDatePicker.appendChild(daySelect);
          
          // Create a remove button
          const removeButton = document.createElement('button');
          removeButton.type = 'button';
          removeButton.textContent = '削除';
          removeButton.className = 'remove-button';
          removeButton.addEventListener('click', function() {
            this.parentElement.remove();
            
            // Show the add button again when the second date option is removed
            addButtonContainer.style.display = '';
          });
          
          // Create a container for the new date picker and remove button
          const datePickerContainer = document.createElement('div');
          datePickerContainer.className = 'second-date-option';
          datePickerContainer.appendChild(newDatePicker);
          datePickerContainer.appendChild(removeButton);
          
          // Find the add button container
          const addButtonContainer = inputInner.querySelector('.add');
          
          // Insert the new date picker after the first date picker but before the add button
          inputInner.insertBefore(datePickerContainer, addButtonContainer);
          
          // Hide the add button after adding the second date option
          addButtonContainer.style.display = 'none';
        });
      }
    }
  }

  // 資格・免許の追加機能
  const licenseSection = document.getElementById('licence1');
  if (licenseSection) {
    const licenseAddButton = licenseSection.querySelector('.add button');
    if (licenseAddButton) {
      licenseAddButton.addEventListener('click', function(event) {
        event.preventDefault(); // 念のためデフォルト動作を防止
        
        // ボタンがある .add の親要素を取得 (.is-input_inner)
        const inputInner = licenseAddButton.closest('.is-input_inner');
        if (!inputInner) return; // 念のためnullチェック
        
        // Find the number of existing checkboxes
        const existingCheckboxes = inputInner.querySelectorAll('.checkbox-group .checkbox-option').length;
        let nextLicenseNumber = existingCheckboxes + 1; // Increment to get the next number
        
        // 新しい checkbox-group を作成
        const newCheckboxGroup = document.createElement('div');
        newCheckboxGroup.className = 'checkbox-group';
        
        // 新しい checkbox-group の中身を設定
        newCheckboxGroup.innerHTML = `
          <label class="checkbox-option">
            <input type="checkbox" name="license1" value="license${nextLicenseNumber}"> 資格名
          </label>
        `;
        
        // ボタンの親要素 (.add) の前に新しい checkbox-group を挿入
        const addButtonContainer = licenseAddButton.closest('.add');
        inputInner.insertBefore(newCheckboxGroup, addButtonContainer);
      });
    }
  }
});
