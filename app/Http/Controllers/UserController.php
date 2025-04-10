<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Prefix;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // แสดงรายการบุคลากร
    public function index()
    {
        $users = User::with('prefix')->paginate(10); // ดึงข้อมูลบุคลากรพร้อมคำนำหน้า
        return view('livewire.users.show', compact('users'));
    }
    // ค้นหาบุคลากร
    public function search(Request $request)
    {
        $query = $request->input('query'); // ค้นหาจากชื่อผู้ใช้
        $userType = $request->input('user_type'); // ค้นหาจากประเภทผู้ใช้

        $users = User::with('prefix');

        if (!empty($query)) { // ค้นหาจากชื่อผู้ใช้
            $users->where('username', 'like', "%{$query}%");
        }

        if (!empty($userType)) { // ค้นหาจากประเภทผู้ใช้
            $users->where('user_type', $userType);
        }

        $users = $users->paginate(10); // แสดงผลลัพธ์ที่ค้นพบ

        return view('livewire.users.show', compact('users')); // ส่งผลลัพธ์ที่ค้นพบไปยัง view
    }
    // แสดงฟอร์มเพิ่มบุคลากร
    public function create()
    {
        $prefixes = Prefix::all(); // ดึงคำนำหน้าทั้งหมดจากฐานข้อมูล
        return view('livewire.users.add', compact('prefixes'));
    }
    // แสดงข้อมูลบุคลากร
    public function show($id)
    {
        $user = User::findOrFail($id); // ดึงข้อมูลบุคลากรตาม ID
        return view('livewire.users.show', compact('user'));
    }

    // บันทึกข้อมูลบุคลากรใหม่
    public function store(Request $request)
    {
        // dd($request->all()); // ตรวจสอบค่าที่ถูกส่งมาจากฟอร์ม
        $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'prefix' => 'required|exists:prefixes,id',
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'user_type' => 'required|string|in:ผู้ดูแลระบบ,เจ้าหน้าที่สาขา,ผู้ปฏิบัติงานบริหาร,อาจารย์',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'username' => $request->username,
            'prefix_id' => $request->prefix,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'user_type' => $request->user_type,
            'email' => $request->email,
            'password' => Hash::make($request->password), // เข้ารหัสรหัสผ่าน
        ]);

        return redirect()->route('user')->with('success', 'เพิ่มบุคลากรเรียบร้อยแล้ว');
    }

    // แสดงฟอร์มแก้ไขบุคลากร
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $prefixes = Prefix::all();
        return view('livewire.users.edit', compact('user', 'prefixes'));
    }

    // อัปเดตข้อมูลบุคลากร
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'prefix' => 'required|exists:prefixes,id',
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'user_type' => 'required|string|in:ผู้ดูแลระบบ,เจ้าหน้าที่สาขา,ผู้ปฏิบัติงานบริหาร,อาจารย์',
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8', // ถ้าไม่กรอก จะไม่เปลี่ยนรหัสผ่าน
        ]);

        $user->update([
            'username' => $request->username,
            'prefix_id' => $request->prefix,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'user_type' => $request->user_type,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password, // เปลี่ยนรหัสผ่านถ้ามีการกรอก
        ]);

        return redirect()->route('user')->with('success', 'แก้ไขข้อมูลเรียบร้อยแล้ว');
    }
    // ลบบุคลากร
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // ลบข้อมูลบุคลากร
        return redirect()->route('user')->with('success', 'ลบบุคลากรเรียบร้อยแล้ว');
    }

    public function profile()
    {
        $user = auth()->user(); // ดึงข้อมูลผู้ใช้ที่ล็อกอิน
        return view('profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8', // ไม่บังคับเปลี่ยนรหัสผ่าน
        ]);

        $user->update([
            'username' => $request->username,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password, // เปลี่ยนรหัสผ่านถ้ามีการกรอก
        ]);

        return redirect()->route('profile')->with('success', 'อัปเดตโปรไฟล์เรียบร้อยแล้ว');
    }

}
