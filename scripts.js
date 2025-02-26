// إظهار أو إخفاء خانة وصف الحرفة بناءً على الدور المختار
function toggleCraftDescription() {
    const craftsmanOption = document.querySelector("input[value='craftsman']");
    const craftDescription = document.getElementById("craft_description");

    // إذا تم اختيار حرفي، أظهر خانة الوصف
    if (craftsmanOption.checked) {
        craftDescription.style.display = "block";
        craftDescription.setAttribute("required", "true"); // اجعلها مطلوبة
    } else {
        // إخفاء خانة الوصف عند اختيار زبون
        craftDescription.style.display = "none";
        craftDescription.removeAttribute("required"); // أزل شرط الإلزام
    }
}

// التحقق من صحة كلمة المرور
function validatePassword() {
    const password = document.querySelector("input[name='password']");
    if (password.value.length < 8) {
        alert("يجب أن تكون كلمة المرور 8 أحرف على الأقل.");
        return false;
    }
    return true;
}

// منع إرسال النموذج إذا كان هناك خطأ
const form = document.querySelector("form");
if (form) {
    form.addEventListener("submit", (event) => {
        if (!validatePassword()) {
            event.preventDefault();
        }
    });
}
