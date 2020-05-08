<?php

namespace Haxifang\Users\WithAuth;

trait AuthHelper
{
    protected $modelClass    = 'App\User';
    protected $deviceIDField = 'uuid';
    protected $accountField  = 'account';

    /**
     * UUID登录（通过设备唯一识别码）
     */
    public function autoSignIn(string $uuid)
    {
        return $this->modelClass::where($this->deviceIDField, $uuid)->first();
    }

    /**
     * UUID注册（可传递其他数据作为注册默认数据）
     */
    public function autoSignUp(string $uuid, array $createData = null)
    {
        return $this->modelClass::crate(array_merge([$this->deviceIDField => $uuid], $createData));
    }

    /**
     * 通过account登录,如果登录账户的设备ID与传入设备ID不同,则会更新登录账户设备ID
     */
    public function signIn(string $account, string $uuid)
    {
        $user = $this->modelClass::where($this->accountField, $account);
        if ($user) {
            if (!strcmp($user->uuid, $uuid)) {
                $user->update([$this->deviceIDField => $uuid]);
            }
            return $user;
        }
    }

    /**
     * 注册
     */
    public function signUp(string $account, string $uuid, array $createData = null)
    {
        $signUpData = array_merge($createData, [
            $this->accountField  => $account,
            $this->deviceIDField => $uuid,
        ]);
        return $this->modelClass::create($signUpData);
    }
}
